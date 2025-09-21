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
 * @property int $id
 * @property \Illuminate\Support\Carbon $date_start
 * @property \Illuminate\Support\Carbon $date_end
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
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
 * @property int $id
 * @property int $examen_id Examen concerné
 * @property int|null $session_exam_id Référence à la session d'examen
 * @property int|null $ec_id
 * @property string $code_complet Code complet d'anonymat (Ex: TA1, SA2)
 * @property int|null $sequence Numéro séquentiel (Ex: 1 dans TA1)
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Copie> $allCopies
 * @property-read int|null $all_copies_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Manchette> $allManchettes
 * @property-read int|null $all_manchettes_count
 * @property-read \App\Models\Copie|null $copie
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Copie> $copies
 * @property-read int|null $copies_count
 * @property-read \App\Models\EC|null $ec
 * @property-read \App\Models\Examen $examen
 * @property-read mixed $code_salle
 * @property-read mixed $etudiant
 * @property-read mixed $numero
 * @property-read mixed $salle
 * @property-read \App\Models\Manchette|null $manchette
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Manchette> $manchettes
 * @property-read int|null $manchettes_count
 * @property-read \App\Models\SessionExam|null $sessionExam
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat complete($sessionId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat forSession($sessionId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat unused($sessionId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat whereCodeComplet($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat whereEcId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat whereExamenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat whereSequence($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat whereSessionExamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat withManchetteOnly($sessionId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat withoutTrashed()
 */
	class CodeAnonymat extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $examen_id Examen concerné
 * @property int|null $session_exam_id Référence à la session d'examen
 * @property int $ec_id Élément constitutif concerné
 * @property int $code_anonymat_id Référence au code d'anonymat
 * @property numeric $note Note obtenue
 * @property numeric|null $note_old Note corrigée
 * @property int $saisie_par Utilisateur ayant saisi la note
 * @property int|null $modifie_par
 * @property string $date_saisie
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
 * @property-read mixed $session_type
 * @property-read \App\Models\ResultatFinal|null $resultatFinal
 * @property-read \App\Models\ResultatFusion|null $resultatFusion
 * @property-read \App\Models\SessionExam|null $sessionExam
 * @property-read \App\Models\User|null $utilisateurModification
 * @property-read \App\Models\User $utilisateurSaisie
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie currentSession()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie forSession($sessionId)
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie sessionNormale()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie sessionRattrapage()
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie whereModifiePar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie whereNoteOld($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie whereSaisiePar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie whereSessionExamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie withoutTrashed()
 */
	class Copie extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $niveau_id
 * @property int|null $parcours_id
 * @property int $session_id
 * @property int $credits_admission_s1
 * @property int $credits_admission_s2
 * @property int $credits_redoublement_s2
 * @property bool $note_eliminatoire_bloque_s1
 * @property bool $note_eliminatoire_exclusion_s2
 * @property bool $delibere
 * @property \Illuminate\Support\Carbon|null $date_deliberation
 * @property int|null $delibere_par
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $deliberePar
 * @property-read \App\Models\Niveau $niveau
 * @property-read \App\Models\Parcour|null $parcours
 * @property-read \App\Models\SessionExam $session
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliberationConfig delibere()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliberationConfig newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliberationConfig newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliberationConfig nonDelibere()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliberationConfig query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliberationConfig whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliberationConfig whereCreditsAdmissionS1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliberationConfig whereCreditsAdmissionS2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliberationConfig whereCreditsRedoublementS2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliberationConfig whereDateDeliberation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliberationConfig whereDelibere($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliberationConfig whereDeliberePar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliberationConfig whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliberationConfig whereNiveauId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliberationConfig whereNoteEliminatoireBloqueS1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliberationConfig whereNoteEliminatoireExclusionS2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliberationConfig whereParcoursId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliberationConfig whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliberationConfig whereUpdatedAt($value)
 */
	class DeliberationConfig extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $niveau_id
 * @property int|null $parcours_id
 * @property string|null $abr Ex: EC1, EC2
 * @property string $nom Ex: Anatomie, Histologie
 * @property numeric $coefficient
 * @property int $ue_id UE à laquelle appartient l'EC
 * @property string $enseignant Enseignant responsable de l'EC
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CodeAnonymat> $codesAnonymat
 * @property-read int|null $codes_anonymat_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ExamenEc> $examenEc
 * @property-read int|null $examen_ec_count
 * @property-read \App\Models\ExamenEc|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Examen> $examens
 * @property-read int|null $examens_count
 * @property-read mixed $libelle_court
 * @property-read mixed $niveau
 * @property-read mixed $nom_complet
 * @property-read mixed $parcours
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ResultatFinal> $resultatsFinaux
 * @property-read int|null $resultats_finaux_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ResultatFusion> $resultatsFusion
 * @property-read int|null $resultats_fusion_count
 * @property-read \App\Models\UE $ue
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC actif()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC avecResultatsSession($sessionId, $useResultatFinal = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC parNiveau($niveauId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC parParcours($parcoursId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC parUE($ueId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC whereAbr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC whereCoefficient($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC whereEnseignant($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC whereNiveauId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC whereParcoursId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC whereUeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EC withoutTrashed()
 */
	class EC extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $matricule Numéro d'identification unique
 * @property string $nom
 * @property string|null $prenom
 * @property \Illuminate\Support\Carbon|null $date_naissance Date de naissance
 * @property int $niveau_id Niveau d'études actuel
 * @property int|null $parcours_id Parcours (uniquement pour PACES/L1)
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read mixed $full_name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Manchette> $manchettes
 * @property-read int|null $manchettes_count
 * @property-read \App\Models\Niveau $niveau
 * @property-read \App\Models\Parcour|null $parcours
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ResultatFinal> $resultatsFinaux
 * @property-read int|null $resultats_finaux_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ResultatFusion> $resultatsFusion
 * @property-read int|null $resultats_fusion_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Etudiant eligiblesRattrapage($niveauId, $parcoursId, $sessionNormaleId)
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Etudiant whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Etudiant withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Etudiant withoutTrashed()
 */
	class Etudiant extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
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
 * @property-read mixed $attached_ec_ids
 * @property-read mixed $codes_grouped_by_e_c
 * @property-read mixed $ecs_grouped_by_u_e
 * @property-read mixed $etudiants_concernes
 * @property-read mixed $status_general
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Manchette> $manchettes
 * @property-read int|null $manchettes_count
 * @property-read \App\Models\Niveau $niveau
 * @property-read \App\Models\Parcour|null $parcours
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Examen whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Examen withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Examen withoutTrashed()
 */
	class Examen extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $salle_id
 * @property int $examen_id
 * @property int $ec_id
 * @property string|null $code_base Code saisi manuellement pour cette matière dans cet examen
 * @property string|null $date_specifique Date spécifique de l'examen (si applicable)
 * @property string|null $heure_specifique Heure spécifique de l'examen (si applicable)
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\EC $ec
 * @property-read \App\Models\Examen $examen
 * @property-read \App\Models\Salle $salle
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExamenEc newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExamenEc newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExamenEc query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExamenEc whereCodeBase($value)
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
 * @property int $id
 * @property int $examen_id Examen concerné
 * @property int|null $session_exam_id Référence à la session d'examen
 * @property int $code_anonymat_id Référence au code d'anonymat
 * @property int $etudiant_id Référence à l'étudiant
 * @property int $saisie_par Utilisateur ayant saisi la manchette
 * @property \Illuminate\Support\Carbon $date_saisie
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
 * @property-read mixed $session_libelle
 * @property-read mixed $session_type
 * @property-read \App\Models\SessionExam|null $sessionExam
 * @property-read \App\Models\User $utilisateurSaisie
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette currentSession()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette forEcAndSession($ecId, $sessionId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette forEtudiantAndSession($etudiantId, $sessionId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette forExamenAndSession($examenId, $sessionId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette sessionNormale()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette sessionRattrapage()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette whereCodeAnonymatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette whereDateSaisie($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette whereEtudiantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette whereExamenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette whereSaisiePar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette whereSessionExamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manchette withoutTrashed()
 */
	class Manchette extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $abr Ex: PACES, L2, L3...
 * @property string $nom
 * @property bool $has_parcours Indique si ce niveau a des parcours
 * @property bool $has_rattrapage Indique si ce niveau a une session de rattrapage
 * @property bool $is_concours Indique si ce niveau est sous forme de concours
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Parcour active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Parcour byNiveau($niveauId)
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
 * @property int $id
 * @property int $examen_id Examen concerné
 * @property int|null $session_exam_id Session d'examen
 * @property int $salle_id Salle concernée
 * @property int|null $ec_id Matière spécifique (optionnel)
 * @property int $etudiants_presents Nombre d'étudiants présents
 * @property array<array-key, mixed> $etudiants_absents Nombre d'étudiants absents
 * @property int|null $total_attendu Total d'étudiants attendus
 * @property string|null $observations Observations sur la présence
 * @property int $saisie_par Utilisateur ayant saisi
 * @property \Illuminate\Support\Carbon $date_saisie
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\EC|null $ec
 * @property-read \App\Models\Examen $examen
 * @property-read int $ecart_attendu
 * @property-read mixed $session_libelle
 * @property-read mixed $session_type
 * @property-read float $taux_presence
 * @property-read int $total_etudiants
 * @property-read \App\Models\Salle $salle
 * @property-read \App\Models\SessionExam|null $sessionExam
 * @property-read \App\Models\User $utilisateurSaisie
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PresenceExamen currentSession()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PresenceExamen forEc($ecId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PresenceExamen forExamen($examenId, $sessionId, $salleId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PresenceExamen newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PresenceExamen newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PresenceExamen onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PresenceExamen query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PresenceExamen sessionNormale()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PresenceExamen sessionRattrapage()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PresenceExamen whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PresenceExamen whereDateSaisie($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PresenceExamen whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PresenceExamen whereEcId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PresenceExamen whereEtudiantsAbsents($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PresenceExamen whereEtudiantsPresents($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PresenceExamen whereExamenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PresenceExamen whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PresenceExamen whereObservations($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PresenceExamen whereSaisiePar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PresenceExamen whereSalleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PresenceExamen whereSessionExamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PresenceExamen whereTotalAttendu($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PresenceExamen whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PresenceExamen withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PresenceExamen withoutTrashed()
 */
	class PresenceExamen extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $etudiant_id Étudiant concerné
 * @property int $examen_id Examen concerné
 * @property int|null $session_exam_id
 * @property int $code_anonymat_id Code d'anonymat utilisé
 * @property int $ec_id
 * @property numeric $note Note finale
 * @property int $genere_par Utilisateur ayant généré le résultat
 * @property int|null $modifie_par
 * @property string $statut
 * @property array<array-key, mixed>|null $status_history
 * @property string|null $motif_annulation
 * @property \Illuminate\Support\Carbon|null $date_annulation
 * @property int|null $annule_par
 * @property \Illuminate\Support\Carbon|null $date_reactivation
 * @property int|null $reactive_par
 * @property string|null $decision
 * @property \Illuminate\Support\Carbon|null $date_publication
 * @property string|null $hash_verification
 * @property bool $jury_validated Indique si la décision a été validée par le jury
 * @property int|null $fusion_id ID du résultat fusion source
 * @property \Illuminate\Support\Carbon|null $date_fusion Date du transfert depuis fusion
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CodeAnonymat $codeAnonymat
 * @property-read \App\Models\EC $ec
 * @property-read \App\Models\Etudiant $etudiant
 * @property-read \App\Models\Examen $examen
 * @property-read mixed $annule_par_actuel
 * @property-read mixed $date_annulation_actuelle
 * @property-read mixed $date_reactivation_actuelle
 * @property-read mixed $derniere_annulation
 * @property-read mixed $derniere_reactivation
 * @property-read mixed $est_eliminatoire
 * @property-read mixed $est_modifie
 * @property-read mixed $est_reussie
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ResultatFinalHistorique> $historique
 * @property-read mixed $libelle_decision
 * @property-read mixed $libelle_statut
 * @property-read mixed $motif_annulation_actuel
 * @property-read mixed $reactive_par_actuel
 * @property-read mixed $session_libelle
 * @property-read mixed $session_type
 * @property-read mixed $status_history_formatted
 * @property-read int|null $historique_count
 * @property-read \App\Models\ResultatFusion|null $resultatFusion
 * @property-read \App\Models\SessionExam|null $sessionExam
 * @property-read \App\Models\User|null $utilisateurAnnulation
 * @property-read \App\Models\User $utilisateurGeneration
 * @property-read \App\Models\User|null $utilisateurModification
 * @property-read \App\Models\User|null $utilisateurReactivation
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal admis()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal annule()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal avecDeliberation()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal echoue()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal eliminatoire()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal enAttente()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal entreSessions($sessionIds)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal etudiantsMultiSessions($examenId, $sessionIds)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal exclus()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal forCurrentSession()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal forSession($sessionId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal parAnneeUniversitaire($anneeId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal parNiveau($niveauId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal parParcours($parcoursId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal pourSession($sessionId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal premiereSession()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal publie()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal publieDans($joursRecents)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal rattrapage()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal rattrapageSession()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal redoublant()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal reussi()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal sansDeliberation()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal sessionNormale()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal sessionRattrapage()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereAnnulePar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereCodeAnonymatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereDateAnnulation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereDateFusion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereDatePublication($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereDateReactivation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereDecision($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereEcId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereEtudiantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereExamenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereFusionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereGenerePar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereHashVerification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereJuryValidated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereModifiePar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereMotifAnnulation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereReactivePar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereSessionExamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereStatusHistory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereStatut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinal whereUpdatedAt($value)
 */
	class ResultatFinal extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $resultat_final_id Référence vers le résultat final
 * @property string $type_action
 * @property string|null $statut_precedent Statut avant l'action
 * @property string|null $statut_nouveau Nouveau statut après l'action
 * @property int $user_id Utilisateur ayant effectué l'action
 * @property string|null $motif Motif de l'action (pour annulation par exemple)
 * @property string|null $decision_precedente
 * @property array<array-key, mixed>|null $donnees_supplementaires Données supplémentaires selon le type d'action
 * @property \Illuminate\Support\Carbon $date_action Date et heure de l'action
 * @property string|null $decision_nouvelle
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $libelle_type_action
 * @property-read \App\Models\ResultatFinal $resultatFinal
 * @property-read \App\Models\User $utilisateur
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinalHistorique changementsDecision()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinalHistorique deliberations()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinalHistorique newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinalHistorique newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinalHistorique ordreAntichronologique()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinalHistorique ordreChronologique()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinalHistorique parPeriode($dateDebut, $dateFin)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinalHistorique parType($type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinalHistorique parUtilisateur($userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinalHistorique query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinalHistorique recent($jours = 30)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinalHistorique whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinalHistorique whereDateAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinalHistorique whereDecisionNouvelle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinalHistorique whereDecisionPrecedente($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinalHistorique whereDonneesSupplementaires($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinalHistorique whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinalHistorique whereMotif($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinalHistorique whereResultatFinalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinalHistorique whereStatutNouveau($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinalHistorique whereStatutPrecedent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinalHistorique whereTypeAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinalHistorique whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFinalHistorique whereUserId($value)
 */
	class ResultatFinalHistorique extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $etudiant_id Étudiant concerné
 * @property int $examen_id Examen concerné
 * @property int|null $session_exam_id
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
 * @property-read \App\Models\Copie|null $copie
 * @property-read \App\Models\EC $ec
 * @property-read \App\Models\Etudiant $etudiant
 * @property-read \App\Models\Examen $examen
 * @property-read mixed $est_eliminatoire
 * @property-read mixed $est_reussie
 * @property-read mixed $session_libelle
 * @property-read mixed $session_type
 * @property-read \App\Models\SessionExam|null $sessionExam
 * @property-read \App\Models\User $utilisateurGeneration
 * @property-read \App\Models\User|null $utilisateurModification
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion echoue()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion eliminatoire()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion forCurrentSession()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion forSession($sessionId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion necessiteVerification()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion parEtape($etape)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion premierVerification()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion reussi()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion secondeVerification()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion sessionNormale()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion sessionRattrapage()
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion whereSessionExamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion whereStatusHistory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion whereStatut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResultatFusion whereUpdatedAt($value)
 */
	class ResultatFusion extends \Eloquent {}
}

namespace App\Models{
/**
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
 * @property int $id
 * @property string|null $abr Ex: UE1, UE2
 * @property string $nom Ex: Médecine humaine, Physiologie
 * @property numeric $credits Nombre de crédits associés à cette UE
 * @property bool $is_active
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UE whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UE whereNiveauId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UE whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UE whereParcoursId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UE whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UE withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UE withoutTrashed()
 */
	class UE extends \Eloquent {}
}

namespace App\Models{
/**
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

