<?php

namespace App\Imports;

use App\Models\Etudiant;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class EtudiantImport implements ToCollection, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    // Propriétés existantes restent inchangées
    protected $niveau_id;
    protected $parcours_id;
    protected $etudiant_count = 0;
    protected $updated_count = 0;

    public function __construct($niveau_id, $parcours_id)
    {
        $this->niveau_id = $niveau_id;
        $this->parcours_id = $parcours_id;
    }

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        // Filtrer les lignes vides immédiatement
        $filteredRows = $rows->filter(function ($row) {
            return !empty($row['matricule']) || !empty($row['nom']);
        });

        foreach ($filteredRows as $row) {
            // Vérification supplémentaire de sécurité
            if (empty($row['matricule']) || empty($row['nom'])) {
                continue;
            }

            // Traiter la valeur is_active
            $is_active = true;
            if (isset($row['is_active'])) {
                $valueToCheck = is_string($row['is_active']) ? strtolower(trim($row['is_active'])) : $row['is_active'];
                $is_active = in_array($valueToCheck, ['true', 'vrai', 'oui', 'yes', '1', 1, true]);
            }

            // Traitement de la date
            $date_naissance = null;
            if (!empty($row['date_naissance'])) {
                $date = trim($row['date_naissance']);
                if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $matches)) {
                    $date_naissance = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
                }
            }

            // Nettoyage du sexe (suppression des espaces)
            $sexe = !empty($row['sexe']) ? trim($row['sexe']) : 'M';

            // Création/mise à jour de l'étudiant
            $etudiant = Etudiant::updateOrCreate(
                ['matricule' => $row['matricule']],
                [
                    'nom' => $row['nom'],
                    'prenom' => $row['prenom'] ?? '',
                    'date_naissance' => $date_naissance,
                    'sexe' => $sexe,
                    'niveau_id' => $this->niveau_id,
                    'parcours_id' => $this->parcours_id,
                    'is_active' => $is_active,
                ]
            );

            // Incrémenter les compteurs
            if ($etudiant->wasRecentlyCreated) {
                $this->etudiant_count++;
            } else {
                $this->updated_count++;
            }
        }

        // Log des résultats
        Log::info('Import Etudiants terminé', [
            'etudiants_créés' => $this->etudiant_count,
            'etudiants_mis_à_jour' => $this->updated_count,
            'niveau_id' => $this->niveau_id,
            'parcours_id' => $this->parcours_id
        ]);
    }

    // Méthode pour spécifier explicitement les noms des en-têtes à rechercher
    public function headingRow(): int
    {
        return 1; // La première ligne contient les en-têtes
    }

    public function rules(): array
    {
        // Assouplir les règles de validation pour éviter les rejets complets
        return [
            '*.matricule' => 'nullable|string|max:20',
            '*.nom' => 'nullable|string|max:50',
            '*.prenom' => 'nullable|string|max:50',
            '*.date_naissance' => 'nullable',
            '*.sexe' => 'nullable',
            '*.is_active' => 'nullable',
        ];
    }

    // Les méthodes suivantes restent identiques
    public function batchSize(): int
    {
        return 300;
    }

    public function chunkSize(): int
    {
        return 300;
    }

    public function getImportCounts()
    {
        return [
            'created' => $this->etudiant_count,
            'updated' => $this->updated_count,
        ];
    }
}
