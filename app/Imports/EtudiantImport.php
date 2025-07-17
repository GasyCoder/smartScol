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
            // CONVERSION MATRICULE : Convertir en string si c'est un nombre
            $matricule = isset($row['matricule']) ? (string) $row['matricule'] : null;
            $nom = isset($row['nom']) ? (string) $row['nom'] : null;
            $prenom = isset($row['prenom']) ? (string) $row['prenom'] : '';

            // Vérification supplémentaire de sécurité
            if (empty($matricule) || empty($nom)) {
                continue;
            }

            // Traiter la valeur is_active
            $is_active = true;
            if (isset($row['is_active'])) {
                $valueToCheck = is_string($row['is_active']) ? strtolower(trim($row['is_active'])) : $row['is_active'];
                $is_active = in_array($valueToCheck, ['true', 'vrai', 'oui', 'yes', '1', 1, true]);
            }

            // TRAITEMENT AMÉLIORÉ DE LA DATE
            $date_naissance = $this->parseDate($row['date_naissance'] ?? null);

            // Création/mise à jour de l'étudiant
            $etudiant = Etudiant::updateOrCreate(
                ['matricule' => $matricule],
                [
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'date_naissance' => $date_naissance,
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

    /**
     * NOUVELLE MÉTHODE : Parse les dates Excel en différents formats
     */
    private function parseDate($dateValue)
    {
        // Si la valeur est vide ou null
        if (empty($dateValue)) {
            return null;
        }

        // Convertir en string pour traitement
        $dateString = trim((string) $dateValue);
        
        // Si c'est vide après trim
        if (empty($dateString)) {
            return null;
        }

        try {
            // CAS 1: Date au format Excel numérique (ex: 44927)
            if (is_numeric($dateString) && strlen($dateString) >= 4) {
                // Excel compte les jours depuis le 1er janvier 1900
                $excelStartDate = new \DateTime('1900-01-01');
                $excelStartDate->modify('-2 days'); // Correction pour le bug Excel
                $excelStartDate->modify('+' . intval($dateString) . ' days');
                return $excelStartDate->format('Y-m-d');
            }

            // CAS 2: Format français dd/mm/yyyy ou dd/mm/yy
            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{2,4})$/', $dateString, $matches)) {
                $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                $year = $matches[3];
                
                // Gérer les années à 2 chiffres
                if (strlen($year) == 2) {
                    $year = (intval($year) <= 30) ? '20' . $year : '19' . $year;
                }
                
                return $year . '-' . $month . '-' . $day;
            }

            // CAS 3: Format avec tirets dd-mm-yyyy
            if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{2,4})$/', $dateString, $matches)) {
                $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                $year = $matches[3];
                
                if (strlen($year) == 2) {
                    $year = (intval($year) <= 30) ? '20' . $year : '19' . $year;
                }
                
                return $year . '-' . $month . '-' . $day;
            }

            // CAS 4: Format ISO yyyy-mm-dd (déjà correct)
            if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $dateString)) {
                $date = new \DateTime($dateString);
                return $date->format('Y-m-d');
            }

            // CAS 5: Essayer avec DateTime pour autres formats
            $date = new \DateTime($dateString);
            return $date->format('Y-m-d');

        } catch (\Exception $e) {
            // Log l'erreur pour debug mais ne pas arrêter l'import
            Log::warning('Impossible de parser la date: ' . $dateString, [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    // Méthode pour spécifier explicitement les noms des en-têtes à rechercher
    public function headingRow(): int
    {
        return 1; // La première ligne contient les en-têtes
    }


    public function rules(): array
    {
        // Assouplir les règles de validation pour accepter les nombres d'Excel
        return [
            '*.matricule' => 'nullable|max:20',      // Accepte nombres ET strings
            '*.nom' => 'nullable|max:50',            // Accepte nombres ET strings
            '*.prenom' => 'nullable|max:50',         // Accepte nombres ET strings  
            '*.date_naissance' => 'nullable',
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
