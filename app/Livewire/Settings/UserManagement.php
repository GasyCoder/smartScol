<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Livewire\Component;
use App\Models\Permission;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserManagement extends Component
{
    use WithPagination;

    // Propriétés pour la gestion des utilisateurs
    public $showUserModal = false;
    public $showPasswordModal = false;
    public $editMode = false;
    public $userId = null;
    public $name = '';
    public $username = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $selectedRoles = [];
    public $searchUsers = '';
    public $confirmDelete = false;
    public $userToDelete = null;
    // Propriétés pour le changement de mot de passe
    public $userForPasswordChange = null;
    public $newPassword = '';
    public $newPassword_confirmation = '';
    public $currentPassword = ''; // Pour l'admin qui change son propre mot de passe

    protected function getRulesForUser()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'selectedRoles' => 'array',
        ];

        if ($this->editMode && $this->userId) {
            $rules['username'] = 'required|string|max:255|unique:users,username,' . $this->userId;
            $rules['email'] = 'required|string|email|max:255|unique:users,email,' . $this->userId;

            // En mode édition, le mot de passe est optionnel
            if (!empty($this->password)) {
                $rules['password'] = 'string|min:8|confirmed';
            }
        } else {
            // En mode création, le mot de passe est obligatoire
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        return $rules;
    }

    protected function getRulesForPasswordChange()
    {
        $rules = [
            'newPassword' => 'required|string|min:8|confirmed',
        ];

        // Si l'utilisateur change son propre mot de passe ET n'est pas superadmin, il doit fournir l'ancien
        if ($this->userForPasswordChange &&
            $this->userForPasswordChange->id === Auth::id() &&
            !Auth::user()->hasRole('superadmin')) {
            $rules['currentPassword'] = 'required|string';
        }

        return $rules;
    }


    public function mount()
    {
        // Vérifier la permission users.manage
        if (!Auth::user()->can('users.manage')) {
            abort(403, 'Vous n\'avez pas l\'autorisation d\'accéder à cette page.');
        }

        $this->resetUserForm();
    }

    public function createUser()
    {
        $this->resetUserForm();
        $this->editMode = false;
        $this->showUserModal = true;
    }

    public function editUser($id)
    {
        try {
            $user = User::with('roles')->findOrFail($id);

            $this->userId = $user->id;
            $this->name = $user->name;
            $this->username = $user->username;
            $this->email = $user->email;
            $this->selectedRoles = $user->roles->pluck('id')->map(function($id) {
                return (int) $id;
            })->toArray();

            $this->editMode = true;
            $this->showUserModal = true;

        } catch (\Exception $e) {
            toastr()->error('Erreur lors de l\'ouverture de l\'utilisateur: ' . $e->getMessage());
        }
    }

    public function saveUser()
    {
        try {
            $this->validate($this->getRulesForUser());

            $userData = [
                'name' => $this->name,
                'username' => $this->username,
                'email' => $this->email,
            ];

            if ($this->editMode) {
                $user = User::findOrFail($this->userId);

                // Ajouter le mot de passe seulement s'il a été renseigné
                if (!empty($this->password)) {
                    $userData['password'] = Hash::make($this->password);
                }

                $user->update($userData);
                $message = 'Utilisateur modifié avec succès.';
            } else {
                $userData['password'] = Hash::make($this->password);
                $user = User::create($userData);
                $message = 'Utilisateur créé avec succès.';
            }

            // Synchroniser les rôles
            $roles = Role::whereIn('id', $this->selectedRoles)->get();
            $user->syncRoles($roles);

            // Nettoyer le cache des permissions
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            toastr()->success($message);
            $this->closeUserModal();

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMessages = [];
            foreach ($e->errors() as $field => $messages) {
                $errorMessages[] = implode(', ', $messages);
            }
            toastr()->error('Erreur de validation: ' . implode(' | ', $errorMessages));

        } catch (\Exception $e) {
            toastr()->error('Une erreur est survenue lors de l\'enregistrement: ' . $e->getMessage());
        }
    }

    public function deleteUser()
    {
        try {
            if (!$this->userToDelete) {
                toastr()->error('Aucun utilisateur sélectionné pour la suppression.');
                return;
            }

            $userName = $this->userToDelete->name;
            $this->userToDelete->delete();

            // Réinitialiser les variables
            $this->confirmDelete = false;
            $this->userToDelete = null;

            toastr()->success("L'utilisateur {$userName} a été supprimé avec succès.");

        } catch (\Exception $e) {
            toastr()->error('Erreur lors de la suppression: ' . $e->getMessage());
            $this->confirmDelete = false;
            $this->userToDelete = null;
        }
    }

    public function cancelDelete()
    {
        $this->confirmDelete = false;
        $this->userToDelete = null;
    }

    public function openPasswordModal($userId)
    {
        try {
            $user = User::findOrFail($userId);

            // Vérifier si l'utilisateur a le droit de changer ce mot de passe
            if ($user->id !== Auth::id() && !Auth::user()->hasRole('superadmin')) {
                toastr()->error('Vous n\'avez pas l\'autorisation de modifier ce mot de passe.');
                return;
            }

            $this->userForPasswordChange = $user;
            $this->newPassword = '';
            $this->newPassword_confirmation = '';
            $this->currentPassword = '';
            $this->showPasswordModal = true;

        } catch (\Exception $e) {
            toastr()->error('Erreur lors de l\'ouverture du changement de mot de passe: ' . $e->getMessage());
        }
    }

    public function changePassword()
    {
        try {
            $this->validate($this->getRulesForPasswordChange());

            // Vérifier l'ancien mot de passe seulement si ce n'est pas un superadmin qui change le mot de passe d'un autre utilisateur
            if ($this->userForPasswordChange->id === Auth::id() &&
                !Auth::user()->hasRole('superadmin')) {
                if (!Hash::check($this->currentPassword, $this->userForPasswordChange->password)) {
                    toastr()->error('Le mot de passe actuel est incorrect.');
                    return;
                }
            }

            // Mettre à jour le mot de passe
            $this->userForPasswordChange->update([
                'password' => Hash::make($this->newPassword)
            ]);

            // Message différent selon le contexte
            if ($this->userForPasswordChange->id === Auth::id()) {
                $message = 'Votre mot de passe a été modifié avec succès.';
            } else {
                $message = 'Le mot de passe de ' . $this->userForPasswordChange->name . ' a été modifié avec succès.';
            }

            toastr()->success($message);
            $this->closePasswordModal();

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMessages = [];
            foreach ($e->errors() as $field => $messages) {
                $errorMessages[] = implode(', ', $messages);
            }
            toastr()->error('Erreur de validation: ' . implode(' | ', $errorMessages));

        } catch (\Exception $e) {
            toastr()->error('Une erreur est survenue lors du changement de mot de passe: ' . $e->getMessage());
        }
    }

    public function closeUserModal()
    {
        $this->showUserModal = false;
        $this->resetUserForm();
    }

    public function closePasswordModal()
    {
        $this->showPasswordModal = false;
        $this->userForPasswordChange = null;
        $this->newPassword = '';
        $this->newPassword_confirmation = '';
        $this->currentPassword = '';
    }

    public function resetUserForm()
    {
        $this->userId = null;
        $this->name = '';
        $this->username = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->selectedRoles = [];

        // Réinitialiser aussi les variables de suppression
        $this->confirmDelete = false;
        $this->userToDelete = null;
    }

    public function updatedSearchUsers()
    {
        $this->resetPage();
    }


    public function confirmDeleteUser($userId)
    {
        try {
            $user = User::findOrFail($userId);

            // Empêcher la suppression de son propre compte
            if ($user->id === Auth::id()) {
                toastr()->error('Vous ne pouvez pas supprimer votre propre compte.');
                return;
            }

            // Empêcher la suppression du superadmin
            if ($user->hasRole('superadmin')) {
                toastr()->error('Impossible de supprimer un super administrateur.');
                return;
            }

            $this->userToDelete = $user;
            $this->confirmDelete = true;

        } catch (\Exception $e) {
            toastr()->error('Erreur lors de la préparation de la suppression: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // Requête pour les utilisateurs
        $usersQuery = User::with('roles')->withCount('roles');
        if ($this->searchUsers) {
            $usersQuery->where(function($q) {
                $q->where('name', 'like', '%' . $this->searchUsers . '%')
                  ->orWhere('email', 'like', '%' . $this->searchUsers . '%')
                  ->orWhere('username', 'like', '%' . $this->searchUsers . '%');
            });
        }
        $users = $usersQuery->paginate(10);

        // Tous les rôles pour les formulaires
        $allRoles = Role::orderBy('name')->get();

        return view('livewire.settings.user-management', [
            'users' => $users,
            'allRoles' => $allRoles
        ]);
    }
}
