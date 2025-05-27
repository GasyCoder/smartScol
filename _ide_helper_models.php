<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon $date_start
 * @property \Illuminate\Support\Carbon $date_end
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Deliberation> $deliberations
 * @property-read int|null $deliberations_count
 * @property-read mixed $libelle
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SessionExam> $sessionExams
 * @property-read int|null $session_exams_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnneeUniversitaire newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnneeUniversitaire newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnneeUniversitaire query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnneeUniversitaire whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnneeUniversitaire whereDateEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnneeUniversitaire whereDateStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnneeUniversitaire whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnneeUniversitaire whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnneeUniversitaire whereUpdatedAt($value)
 */
	class AnneeUniversitaire extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $examen_id Examen concerné
 * @property int|null $ec_id
 * @property string $code_complet Code complet d'anonymat (Ex: TA1, SA2)
 * @property int|null $sequence Numéro séquentiel (Ex: 1 dans TA1)
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Copie|null $copie
 * @property-read \App\Models\EC|null $ec
 * @property-read \App\Models\Examen $examen
 * @property-read mixed $code_salle
 * @property-read mixed $etudiant
 * @property-read mixed $numero
 * @property-read mixed $salle
 * @property-read \App\Models\Manchette|null $manchette
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat whereCodeComplet($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat whereEcId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat whereExamenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat whereSequence($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat withoutTrashed()
 */
	class CodeAnonymat extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $examen_id Examen concerné
 * @property int $ec_id Élément constitutif concerné
 * @property int $code_anonymat_id Référence au code d'anonymat
 * @property numeric $note Note obtenue
 * @property int $saisie_par Utilisateur ayant saisi la note
 * @property string $date_saisie
 * @property numeric|null $note_old Note corrigée
 * @property bool $is_checked
 * @property string|null $commentaire Commentaire sur la note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\CodeAnonymat $codeAnonymat
 * @property-read \App\Models\EC $ec
 * @property-read \App\Models\Examen $examen
 * @property-read mixed $code_complet
 * @property-read mixed $code_salle
 * @property-read mixed $etudiant
 * @property-read mixed $numero
 * @property-read \App\Models\ResultatFinal|null $resultatFinal
 * @property-read \App\Models\ResultatFusion|null $resultatFusion
 * @property-read \App\Models\User $utilisateurSaisie
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie modifiees()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie nonModifiees()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie nonVerifiees()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie parEtudiant($etudiantId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie parNiveau($niveauId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie parParcours($parcoursId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie parSession($sessionId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie verifiees()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie whereCodeAnonymatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie whereCommentaire($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie whereDateSaisie($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie whereEcId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie whereExamenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie whereIsChecked($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie whereNoteOld($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie whereSaisiePar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie withoutTrashed()
 */
	class Copie extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $niveau_id Niveau concerné
 * @property int $session_id Session d'examen
 * @property int|null $examen_id
 * @property int $annee_universitaire_id Année universitaire
 * @property \Illuminate\Support\Carbon $date_deliberation
 * @property string $statut Statut de la délibération
 * @property numeric $seuil_admission Moyenne minimale pour admission automatique
 * @property numeric $seuil_rachat Moyenne minimale pour rachat possible
 * @property int $pourcentage_ue_requises Pourcentage d'UE à valider pour admission
 * @property bool $appliquer_regles_auto Appliquer automatiquement les règles aux étudiants
 * @property string|null $observations Observations du jury
 * @property array<array-key, mixed>|null $decisions_speciales Décisions spéciales prises pendant la délibération
 * @property int $nombre_admis Nombre d'étudiants admis
 * @property int $nombre_ajournes Nombre d'étudiants ajournés
 * @property int $nombre_exclus Nombre d'étudiants exclus
 * @property int $nombre_rachats Nombre d'étudiants rachetés
 * @property \Illuminate\Support\Carbon|null $date_finalisation Date de finalisation des décisions
 * @property \Illuminate\Support\Carbon|null $date_publication Date de publication des résultats
 * @property int|null $finalise_par Utilisateur ayant finalisé la délibération
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AnneeUniversitaire $anneeUniversitaire
 * @property-read \App\Models\Examen|null $examen
 * @property-read \App\Models\User|null $finalisePar
 * @property-read mixed $libelle_statut
 * @property-read \App\Models\Niveau $niveau
 * @property-read \App\Models\User|null $presidentJury
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ResultatFinal> $resultatsFinaux
 * @property-read int|null $resultats_finaux_count
 * @property-read \App\Models\SessionExam $session
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation activeAnnee()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation enAttente()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation niveauxReguliers()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation rattrapage()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation statut($statut)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation terminees()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation whereAnneeUniversitaireId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation whereAppliquerReglesAuto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation whereDateDeliberation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation whereDateFinalisation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation whereDatePublication($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation whereDecisionsSpeciales($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation whereExamenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation whereFinalisePar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation whereNiveauId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation whereNombreAdmis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation whereNombreAjournes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation whereNombreExclus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation whereNombreRachats($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation whereObservations($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation wherePourcentageUeRequises($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation whereSeuilAdmission($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation whereSeuilRachat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation whereStatut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation whereUpdatedAt($value)
 */
	class Deliberation extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $abr Ex: EC1, EC2
 * @property string $nom Ex: Anatomie, Histologie
 * @property numeric $coefficient
 * @property int $ue_id UE à laquelle appartient l'EC
 * @property string $enseignant Enseignant responsable de l'EC
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ExamenEc> $examenEc
 * @property-read int|null $examen_ec_count
 * @property-read \App\Models\ExamenEc|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Examen> $examens
 * @property-read int|null $examens_count
 * @property-read mixed $niveau
 * @property-read mixed $parcours
 * @property-read \App\Models\UE $ue
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC whereAbr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC whereCoefficient($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC whereEnseignant($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC whereUeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC withoutTrashed()
 */
	class EC extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $matricule Numéro d'identification unique
 * @property string $nom
 * @property string|null $prenom
 * @property string $sexe M ou F
 * @property \Illuminate\Support\Carbon|null $date_naissance Date de naissance
 * @property int $niveau_id Niveau d'études actuel
 * @property int|null $parcours_id Parcours (uniquement pour PACES/L1)
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read mixed $full_name
 * @property-read \App\Models\Niveau $niveau
 * @property-read \App\Models\Parcour|null $parcours
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Etudiant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Etudiant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Etudiant onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Etudiant paces()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Etudiant query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Etudiant superieurs()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Etudiant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Etudiant whereDateNaissance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Etudiant whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Etudiant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Etudiant whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Etudiant whereMatricule($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Etudiant whereNiveauId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Etudiant whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Etudiant whereParcoursId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Etudiant wherePrenom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Etudiant whereSexe($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Etudiant whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Etudiant withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Etudiant withoutTrashed()
 */
	class Etudiant extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $session_id Session à laquelle appartient l'examen
 * @property int $niveau_id Niveau concerné
 * @property int|null $parcours_id Parcours concerné (uniquement pour PACES/L1)
 * @property int $duree Durée en minutes
 * @property numeric|null $note_eliminatoire Note éliminatoire pour les concours
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CodeAnonymat> $codesAnonymat
 * @property-read int|null $codes_anonymat_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Copie> $copies
 * @property-read int|null $copies_count
 * @property-read \App\Models\ExamenEc|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EC> $ecs
 * @property-read int|null $ecs_count
 * @property-read mixed $codes_grouped_by_e_c
 * @property-read mixed $ecs_grouped_by_u_e
 * @property-read mixed $etudiants_concernes
 * @property-read mixed $first_date
 * @property-read mixed $first_heure_debut
 * @property-read mixed $first_salle
 * @property-read mixed $status_general
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Manchette> $manchettes
 * @property-read int|null $manchettes_count
 * @property-read \App\Models\Niveau $niveau
 * @property-read \App\Models\Parcour|null $parcours
 * @property-read \App\Models\SessionExam $session
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Examen newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Examen newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Examen onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Examen query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Examen whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Examen whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Examen whereDuree($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Examen whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Examen whereNiveauId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Examen whereNoteEliminatoire($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Examen whereParcoursId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Examen whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Examen whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Examen withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Examen withoutTrashed()
 */
	class Examen extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $salle_id
 * @property int $examen_id
 * @property int $ec_id
 * @property string|null $date_specifique Date spécifique de l'examen (si applicable)
 * @property string|null $heure_specifique Heure spécifique de l'examen (si applicable)
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Salle $salle
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExamenEc newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExamenEc newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExamenEc query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExamenEc whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExamenEc whereDateSpecifique($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExamenEc whereEcId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExamenEc whereExamenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExamenEc whereHeureSpecifique($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExamenEc whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExamenEc whereSalleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExamenEc whereUpdatedAt($value)
 */
	class ExamenEc extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $examen_id Examen concerné
 * @property int $code_anonymat_id Référence au code d'anonymat
 * @property int $etudiant_id Référence à l'étudiant
 * @property int $saisie_par Utilisateur ayant saisi la manchette
 * @property string $date_saisie
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\CodeAnonymat $codeAnonymat
 * @property-read \App\Models\Etudiant $etudiant
 * @property-read \App\Models\Examen $examen
 * @property-read mixed $code_anonymat_complet
 * @property-read mixed $code_salle
 * @property-read mixed $ec
 * @property-read mixed $matricule_etudiant
 * @property-read mixed $numero
 * @property-read mixed $salle
 * @property-read \App\Models\User $utilisateurSaisie
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette whereCodeAnonymatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette whereDateSaisie($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette whereEtudiantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette whereExamenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette whereSaisiePar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette withoutTrashed()
 */
	class Manchette extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $abr Ex: PACES, L2, L3...
 * @property string $nom
 * @property bool $has_parcours Indique si ce niveau a des parcours
 * @property bool $has_rattrapage Indique si ce niveau a une session de rattrapage
 * @property bool $is_concours Indique si ce niveau est sous forme de concours
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Deliberation> $deliberations
 * @property-read int|null $deliberations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Etudiant> $etudiants
 * @property-read int|null $etudiants_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Examen> $examens
 * @property-read int|null $examens_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Parcour> $parcours
 * @property-read int|null $parcours_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UE> $ues
 * @property-read int|null $ues_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Niveau avecRattrapage()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Niveau concours()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Niveau newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Niveau newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Niveau query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Niveau whereAbr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Niveau whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Niveau whereHasParcours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Niveau whereHasRattrapage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Niveau whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Niveau whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Niveau whereIsConcours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Niveau whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Niveau whereUpdatedAt($value)
 */
	class Niveau extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $abr Ex: MG, DENT, INF
 * @property string $nom Ex: Médecine générale, Dentaire, Infirmier
 * @property int $niveau_id Niveau auquel appartient ce parcours
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Etudiant> $etudiants
 * @property-read int|null $etudiants_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Examen> $examens
 * @property-read int|null $examens_count
 * @property-read \App\Models\Niveau $niveau
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UE> $ues
 * @property-read int|null $ues_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Parcour newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Parcour newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Parcour query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Parcour whereAbr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Parcour whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Parcour whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Parcour whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Parcour whereNiveauId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Parcour whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Parcour whereUpdatedAt($value)
 */
	class Parcour extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string|null $label
 * @property string|null $description
 * @property string $guard_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission withoutRole($roles, $guard = null)
 */
	class Permission extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $etudiant_id Étudiant concerné
 * @property int $examen_id Examen concerné
 * @property int $code_anonymat_id Code d'anonymat utilisé
 * @property int $ec_id
 * @property numeric $note Note finale
 * @property int $genere_par Utilisateur ayant généré le résultat
 * @property int|null $modifie_par
 * @property string $statut
 * @property string|null $status_history
 * @property string|null $motif_annulation
 * @property string|null $date_annulation
 * @property int|null $annule_par
 * @property string|null $date_reactivation
 * @property int|null $reactive_par
 * @property string|null $decision
 * @property \Illuminate\Support\Carbon|null $date_publication
 * @property string|null $hash_verification
 * @property int|null $deliberation_id
 * @property int|null $fusion_id ID du résultat fusion source
 * @property \Illuminate\Support\Carbon|null $date_fusion Date du transfert depuis fusion
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CodeAnonymat $codeAnonymat
 * @property-read \App\Models\Deliberation|null $deliberation
 * @property-read \App\Models\ResultatFinalHistorique|null $derniereAnnulation
 * @property-read \App\Models\ResultatFinalHistorique|null $derniereReactivation
 * @property-read \App\Models\EC $ec
 * @property-read \App\Models\Etudiant $etudiant
 * @property-read \App\Models\Examen $examen
 * @property-read mixed $est_eliminatoire
 * @property-read mixed $est_modifie
 * @property-read mixed $est_reussie
 * @property-read mixed $libelle_decision
 * @property-read mixed $libelle_statut
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ResultatFinalHistorique> $historique
 * @property-read int|null $historique_count
 * @property-read \App\Models\ResultatFusion|null $resultatFusion
 * @property-read \App\Models\User $utilisateurGeneration
 * @property-read \App\Models\User|null $utilisateurModification
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal admis()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal annule()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal avecDeliberation()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal echoue()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal eliminatoire()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal enAttente()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal exclus()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal parAnneeUniversitaire($anneeId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal parNiveau($niveauId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal parParcours($parcoursId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal premiereSession()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal publie()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal publieDans($joursRecents)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal rattrapage()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal rattrapageSession()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal redoublant()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal reussi()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal sansDeliberation()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereAnnulePar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereCodeAnonymatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereDateAnnulation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereDateFusion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereDatePublication($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereDateReactivation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereDecision($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereDeliberationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereEcId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereEtudiantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereExamenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereFusionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereGenerePar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereHashVerification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereModifiePar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereMotifAnnulation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereReactivePar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereStatusHistory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereStatut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereUpdatedAt($value)
 */
	class ResultatFinal extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property-read mixed $libelle_type_action
 * @property-read \App\Models\ResultatFinal|null $resultatFinal
 * @property-read \App\Models\User|null $utilisateur
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinalHistorique newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinalHistorique newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinalHistorique ordreAntichronologique()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinalHistorique ordreChronologique()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinalHistorique parPeriode($dateDebut, $dateFin)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinalHistorique parType($type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinalHistorique parUtilisateur($userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinalHistorique query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinalHistorique recent($jours = 30)
 */
	class ResultatFinalHistorique extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $etudiant_id Étudiant concerné
 * @property int $examen_id Examen concerné
 * @property int $code_anonymat_id Code d'anonymat utilisé
 * @property int $ec_id
 * @property numeric $note Note à vérifier
 * @property int $genere_par Utilisateur ayant généré le résultat
 * @property int|null $modifie_par
 * @property int $etape_fusion Compteur de fusion pour éviter les doublons
 * @property string $statut
 * @property array<array-key, mixed>|null $status_history
 * @property \Illuminate\Support\Carbon|null $date_validation
 * @property string|null $operation_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CodeAnonymat $codeAnonymat
 * @property-read \App\Models\EC $ec
 * @property-read \App\Models\Etudiant $etudiant
 * @property-read \App\Models\Examen $examen
 * @property-read mixed $est_eliminatoire
 * @property-read mixed $est_reussie
 * @property-read \App\Models\User $utilisateurGeneration
 * @property-read \App\Models\User|null $utilisateurModification
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion echoue()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion eliminatoire()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion necessiteVerification()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion parEtape($etape)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion premierVerification()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion reussi()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion secondeVerification()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion troisiemeVerification()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion valide()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion whereCodeAnonymatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion whereDateValidation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion whereEcId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion whereEtapeFusion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion whereEtudiantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion whereExamenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion whereGenerePar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion whereModifiePar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion whereOperationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion whereStatusHistory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion whereStatut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion whereUpdatedAt($value)
 */
	class ResultatFusion extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $code_base Première lettre du préfixe (T pour 2P, S pour 2P1, etc.)
 * @property string $nom Ex: 2P, 2P1
 * @property int $capacite Nombre de places
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $capacite_disponible
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salle newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salle newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salle query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salle whereCapacite($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salle whereCodeBase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salle whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salle whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salle whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salle whereUpdatedAt($value)
 */
	class Salle extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $type
 * @property int $annee_universitaire_id
 * @property bool $is_active
 * @property bool $is_current
 * @property \Illuminate\Support\Carbon $date_start
 * @property \Illuminate\Support\Carbon $date_end
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AnneeUniversitaire $anneeUniversitaire
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Deliberation> $deliberations
 * @property-read int|null $deliberations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Examen> $examens
 * @property-read int|null $examens_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionExam activeInActiveYear()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionExam current()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionExam newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionExam newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionExam normale()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionExam query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionExam rattrapage()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionExam whereAnneeUniversitaireId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionExam whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionExam whereDateEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionExam whereDateStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionExam whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionExam whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionExam whereIsCurrent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionExam whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionExam whereUpdatedAt($value)
 */
	class SessionExam extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $abr Ex: UE1, UE2
 * @property string $nom Ex: Médecine humaine, Physiologie
 * @property numeric $credits Nombre de crédits associés à cette UE
 * @property int $niveau_id
 * @property int|null $parcours_id Uniquement pour les UE spécifiques à un parcours (PACES)
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EC> $ecs
 * @property-read int|null $ecs_count
 * @property-read mixed $calculated_credits
 * @property-read \App\Models\Niveau $niveau
 * @property-read \App\Models\Parcour|null $parcours
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UE newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UE newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UE onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UE query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UE whereAbr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UE whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UE whereCredits($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UE whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UE whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UE whereNiveauId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UE whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UE whereParcoursId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UE whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UE withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UE withoutTrashed()
 */
	class UE extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $username
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $initials
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 */
	class User extends \Eloquent {}
}

