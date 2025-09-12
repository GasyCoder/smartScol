<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Réinitialiser le cache des rôles et permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Créer les permissions avec label et description
        $this->createPermissions();

        // Créer les rôles
        $this->createRoles();
    }

    /**
     * Crée toutes les permissions nécessaires pour le système
     */
    private function createPermissions()
    {
        $permissions = [
            // Permissions pour les examens
            [
                'name' => 'etudiants.view',
                'label' => 'Voir les étudiants',

                'description' => 'Permet de consulter la liste des étudiants'
            ],
            [
                'name' => 'etudiants.create',
                'label' => 'Créer des étudiants',
                'description' => 'Permet de créer de nouveaux étudiants'
            ],
            [
                'name' => 'etudiants.edit',
                'label' => 'Modifier des étudiants',
                'description' => 'Permet de modifier les informations des étudiants'
            ],
            [
                'name' => 'etudiants.delete',
                'label' => 'Supprimer des étudiants',
                'description' => 'Permet de supprimer des étudiants du système'
            ],
            [
                'name' => 'examens.view',
                'label' => 'Voir les examens',

                'description' => 'Permet de consulter la liste des examens'
            ],
            [
                'name' => 'examens.create',
                'label' => 'Créer un examen',
                'description' => 'Permet de créer un nouvel examen'
            ],
            [
                'name' => 'examens.edit',
                'label' => 'Modifier un examen',
                'description' => 'Permet de modifier les informations d\'un examen existant'
            ],
            [
                'name' => 'examens.delete',
                'label' => 'Supprimer un examen',
                'description' => 'Permet de supprimer un examen du système'
            ],

            // Permissions pour les copies (notes)
            [
                'name' => 'copies.create',
                'label' => 'Créer des copies',
                'description' => 'Permet de saisir de nouvelles copies'
            ],
            [
                'name' => 'copies.view',
                'label' => 'Voir les notes',
                'description' => 'Permet de consulter les notes saisies'
            ],
            [
                'name' => 'copies.edit',
                'label' => 'Modifier des notes',
                'description' => 'Permet de modifier des notes existantes'
            ],
            [
                'name' => 'copies.delete',
                'label' => 'Supprimer des notes',
                'description' => 'Permet de supprimer des notes du système'
            ],

            // Permissions pour les manchettes
            [
                'name' => 'manchettes.create',
                'label' => 'Créer des manchettes',
                'description' => 'Permet de créer de nouvelles manchettes'
            ],
            [
                'name' => 'manchettes.view',
                'label' => 'Voir les manchettes',
                'description' => 'Permet de consulter les manchettes saisies'
            ],
            [
                'name' => 'manchettes.edit',
                'label' => 'Modifier des manchettes',
                'description' => 'Permet de modifier des manchettes existantes'
            ],
            [
                'name' => 'manchettes.delete',
                'label' => 'Supprimer des manchettes',
                'description' => 'Permet de supprimer des manchettes du système'
            ],

            // Permissions pour les résultats
            [
                'name' => 'resultats.view',
                'label' => 'Voir les résultats',
                'description' => 'Permet de consulter les résultats des examens'
            ],
            [
                'name' => 'resultats.verifier',
                'label' => 'Verifier les résultats',
                'description' => 'Permet de verifier les résultats des examens'
            ],
            [
                'name' => 'resultats.create',
                'label' => 'Générer les résultats',
                'description' => 'Permet de fusionner les copies et manchettes pour générer les résultats'
            ],
            [
                'name' => 'resultats.edit',
                'label' => 'Modifier les résultats',
                'description' => 'Permet de modifier les résultats générés'
            ],
            [
                'name' => 'resultats.delete',
                'label' => 'Supprimer des résultats',
                'description' => 'Permet de supprimer des résultats du système'
            ],
            [
                'name' => 'resultats.validation',
                'label' => 'Valider les résultats',
                'description' => 'Permet de valider les résultats avant publication'
            ],
            [
                'name' => 'resultats.publication',
                'label' => 'Publier les résultats',
                'description' => 'Permet de publier les résultats validés'
            ],
            [
                'name' => 'resultats.impression',
                'label' => 'Imprimer les résultats',
                'description' => 'Permet d\'imprimer les résultats générés'
            ],
            [
                'name' => 'resultats.export',
                'label' => 'Exporter les résultats',
                'description' => 'Permet d\'exporter les résultats au format PDF, Excel, etc.'
            ],
            [
                'name' => 'resultats.fusion',
                'label' => 'Fussioner les résultats',
                'description' => 'Permet de fussioner les résultats manchette et notes'
            ],
            [
                'name' => 'resultats.reset-fusion',
                'label' => 'Réinitialiser la fusion',
                'description' => 'Permet de réinitialiser les résultats provisoires et validés d\'un examen'
            ],
            [
                'name' => 'resultats.cancel',
                'label' => 'Annuler les résultats',
                'description' => 'Permet d\'annuler les résultats d\'un examen'
            ],
            [
                'name' => 'resultats.reactiver',
                'label' => 'Reactiver la résultats',
                'description' => 'Permet de réactiver la validation des résultats d\'un examen'
            ],
            // Permissions pour l'administration
            [
                'name' => 'users.manage',
                'label' => 'Gérer les utilisateurs',
                'description' => 'Permet de créer, modifier et supprimer des utilisateurs'
            ],
            [
                'name' => 'roles.manage',
                'label' => 'Gérer les rôles',
                'description' => 'Permet de créer, modifier et supprimer des rôles et permissions'
            ],
            [
                'name' => 'system.configure',
                'label' => 'Configurer le système',
                'description' => 'Permet de modifier les paramètres globaux du système'
            ],
            [
                'name' => 'users.view',
                'label' => 'Voir les utilisateurs détaillés',
                'description' => 'Permet de consulter la liste détaillée des utilisateurs avec leurs rôles'
            ],
            [
                'name' => 'users.create',
                'label' => 'Créer des utilisateurs',
                'description' => 'Permet de créer de nouveaux comptes utilisateur'
            ],
            [
                'name' => 'users.edit',
                'label' => 'Modifier les utilisateurs',
                'description' => 'Permet de modifier les informations des utilisateurs'
            ],
            [
                'name' => 'users.delete',
                'label' => 'Supprimer les utilisateurs',
                'description' => 'Permet de supprimer des comptes utilisateur'
            ],
            [
                'name' => 'users.change-password',
                'label' => 'Changer les mots de passe',
                'description' => 'Permet de modifier les mots de passe des utilisateurs'
            ],
            [
                'name' => 'users.assign-roles',
                'label' => 'Assigner des rôles',
                'description' => 'Permet d\'assigner et modifier les rôles des utilisateurs'
            ]
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                [
                    'label' => $permission['label'],
                    'description' => $permission['description']
                ]
            );
        }

    }

    /**
     * Crée les rôles et leur attribue les permissions
     */
    private function createRoles()
    {
        // Créer le rôle superadmin
        $superadmin = Role::firstOrCreate(['name' => 'superadmin']);
        $superadmin->syncPermissions(Permission::all());

        // Créer le rôle enseignant
        $enseignant = Role::firstOrCreate(['name' => 'enseignant']);
        $enseignant->syncPermissions([
            'etudiants.view',
            'etudiants.edit',  
            'resultats.view',
            'manchettes.view',
            'manchettes.edit',
            'copies.view',
            'copies.edit',
        ]);

        // Créer le rôle secrétaire
        $secretaire = Role::firstOrCreate(['name' => 'secretaire']);
        $secretaire->syncPermissions([
            'etudiants.view',
            'manchettes.create',
            'copies.create',
        ]);
    }
}