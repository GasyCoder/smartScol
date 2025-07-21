<?php

namespace App\Imports;

use App\Models\UE;
use App\Models\EC;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Notifications\ImportCompleted;
use Illuminate\Support\Facades\Notification;
use App\Models\User;

class UEECImport implements ToCollection, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    protected $niveau_id;
    protected $parcours_id;
    protected $user_id;
    protected $current_ue_id = null;
    protected $ue_count = 0;
    protected $ec_count = 0;

    public function __construct($niveau_id, $parcours_id, $user_id = null)
    {
        $this->niveau_id = $niveau_id;
        $this->parcours_id = $parcours_id;
        $this->user_id = $user_id;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Vérifier si la ligne a au moins les données EC requises
            if (empty($row['ec_abr']) || empty($row['ec_nom'])) {
                continue;
            }

            // Traiter l'UE si les champs UE sont remplis
            if (!empty($row['ue_abr']) && !empty($row['ue_nom'])) {
                // Récupérer les crédits ou utiliser 0 par défaut
                $credits = isset($row['ue_credits']) ? (float)$row['ue_credits'] : 0;

                // C'est une nouvelle UE
                $ue = UE::firstOrCreate(
                    [
                        'abr' => $row['ue_abr'],
                        'niveau_id' => $this->niveau_id,
                        'parcours_id' => $this->parcours_id
                    ],
                    [
                        'nom' => $row['ue_nom'],
                        'credits' => $credits // Ajout du champ crédits
                    ]
                );

                // Si l'UE existe déjà mais que nous avons des crédits à mettre à jour
                if (!$ue->wasRecentlyCreated && isset($row['ue_credits']) && $ue->credits != $credits) {
                    $ue->update(['credits' => $credits]);
                }

                $this->current_ue_id = $ue->id;

                // Si l'UE a été créée (n'existait pas avant), incrémentez le compteur
                if ($ue->wasRecentlyCreated) {
                    $this->ue_count++;
                }
            } elseif ($this->current_ue_id === null) {
                // Si aucune UE n'a été définie et que cette ligne n'a pas de données UE, passez à la ligne suivante
                continue;
            }

            // Traiter l'EC
            $coefficient = !empty($row['coefficient']) ? $row['coefficient'] : 1.0;
            $enseignant = !empty($row['enseignant']) ? $row['enseignant'] : 'Non assigné';

            $ec = EC::firstOrCreate(
                [
                    'abr' => $row['ec_abr'],
                    'ue_id' => $this->current_ue_id
                ],
                [
                    'nom' => $row['ec_nom'],
                    'coefficient' => $coefficient,
                    'enseignant' => $enseignant
                ]
            );

            // Si l'EC a été créé (n'existait pas avant), incrémentez le compteur
            if ($ec->wasRecentlyCreated) {
                $this->ec_count++;
            }
        }

        // Log des résultats
        Log::info('Import UE/EC terminé', [
            'ues_créées' => $this->ue_count,
            'ecs_créés' => $this->ec_count,
            'niveau_id' => $this->niveau_id,
            'parcours_id' => $this->parcours_id
        ]);

        // Envoyer une notification à l'utilisateur qui a lancé l'importation
        if ($this->user_id) {
            $user = User::find($this->user_id);
            if ($user) {
                Notification::send($user, new ImportCompleted($this->ue_count, $this->ec_count));
            }
        }
    }

    public function rules(): array
    {
        return [
            '*.ue_abr' => 'nullable|max:10',           // Supprimé 'string'
            '*.ue_nom' => 'nullable|max:100',          // Supprimé 'string'
            '*.ue_credits' => 'nullable|numeric|min:0',
            '*.ec_abr' => 'nullable|max:10',           // CHANGÉ: 'required|string' → 'nullable'
            '*.ec_nom' => 'nullable|max:100',          // CHANGÉ: 'required|string' → 'nullable'
            '*.coefficient' => 'nullable|numeric|min:0|max:999.9',
            '*.enseignant' => 'nullable|max:100',      // Supprimé 'string'
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.ec_abr.required' => 'L\'abréviation de l\'EC est obligatoire',
            '*.ec_nom.required' => 'Le nom de l\'EC est obligatoire',
            '*.ue_credits.numeric' => 'Les crédits de l\'UE doivent être un nombre',
            '*.ue_credits.min' => 'Les crédits de l\'UE doivent être positifs ou nuls',
        ];
    }

    public function batchSize(): int
    {
        return 300; // Augmenté pour de meilleures performances
    }

    public function chunkSize(): int
    {
        return 300; // Augmenté pour de meilleures performances
    }

    // Méthode pour récupérer les compteurs
    public function getImportCounts()
    {
        return [
            'ues' => $this->ue_count,
            'ecs' => $this->ec_count
        ];
    }
}
