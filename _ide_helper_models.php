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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SchemaCodage> $modelesCodage
 * @property-read int|null $modeles_codage_count
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
 * @property int|null $etudiant_id Référence à l'étudiant
 * @property string $code_complet Code complet d'anonymat (Ex: TA1, SA2)
 * @property int|null $sequence Numéro séquentiel (Ex: 1 dans TA1)
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Copie|null $copie
 * @property-read \App\Models\Etudiant|null $etudiant
 * @property-read \App\Models\Examen $examen
 * @property-read \App\Models\Manchette|null $manchette
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat whereCodeComplet($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodeAnonymat whereEtudiantId($value)
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
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\CodeAnonymat $codeAnonymat
 * @property-read \App\Models\EC $ec
 * @property-read \App\Models\Examen $examen
 * @property-read mixed $code_complet
 * @property-read mixed $code_salle
 * @property-read mixed $numero
 * @property-read \App\Models\Resultat|null $resultat
 * @property-read \App\Models\User $utilisateurSaisie
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie whereCodeAnonymatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie whereDateSaisie($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie whereEcId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie whereExamenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Copie whereNote($value)
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
 * @property int $deliberation_id Délibération concernée
 * @property int $etudiant_id Étudiant concerné
 * @property numeric $moyenne Moyenne générale
 * @property string $decision
 * @property numeric $points_jury Points ajoutés par le jury
 * @property string|null $observations
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Deliberation $deliberation
 * @property-read \App\Models\Etudiant $etudiant
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Decision newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Decision newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Decision query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Decision whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Decision whereDecision($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Decision whereDeliberationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Decision whereEtudiantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Decision whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Decision whereMoyenne($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Decision whereObservations($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Decision wherePointsJury($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Decision whereUpdatedAt($value)
 */
	class Decision extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $niveau_id Niveau concerné
 * @property int $session_id Session d'examen
 * @property int $annee_universitaire_id Année universitaire
 * @property \Illuminate\Support\Carbon $date_deliberation
 * @property int $president_jury Enseignant président du jury
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AnneeUniversitaire $anneeUniversitaire
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Decision> $decisions
 * @property-read int|null $decisions_count
 * @property-read \App\Models\Niveau $niveau
 * @property-read \App\Models\User $presidentJury
 * @property-read \App\Models\SessionExam $session
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation niveauxSuperieurs()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation whereAnneeUniversitaireId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation whereDateDeliberation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation whereNiveauId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation wherePresidentJury($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deliberation whereSessionId($value)
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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Decision> $decisions
 * @property-read int|null $decisions_count
 * @property-read mixed $full_name
 * @property-read \App\Models\Niveau $niveau
 * @property-read \App\Models\Parcour|null $parcours
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Resultat> $resultats
 * @property-read int|null $resultats_count
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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Resultat> $resultats
 * @property-read int|null $resultats_count
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
 * @property-read mixed $code_anonymat
 * @property-read mixed $code_salle
 * @property-read mixed $ec
 * @property-read mixed $matricule_etudiant
 * @property-read mixed $numero
 * @property-read mixed $salle
 * @property-read \App\Models\Resultat|null $resultat
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
 * @property string $guard_name
 * @property string|null $label
 * @property string|null $description
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
 * @property numeric $note Note finale
 * @property int $genere_par Utilisateur ayant généré le résultat
 * @property string $date_generation
 * @property string $statut
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Copie|null $copie
 * @property-read \App\Models\Etudiant $etudiant
 * @property-read \App\Models\Examen $examen
 * @property-read \App\Models\Manchette|null $manchette
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resultat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resultat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resultat query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resultat whereCodeAnonymatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resultat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resultat whereDateGeneration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resultat whereEtudiantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resultat whereExamenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resultat whereGenerePar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resultat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resultat whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resultat whereStatut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resultat whereUpdatedAt($value)
 */
	class Resultat extends \Eloquent {}
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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SchemaCodage> $schemasCodage
 * @property-read int|null $schemas_codage_count
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
 * @property string $nom Ex: Codage 6e année 2024-2025
 * @property int $niveau_id Niveau concerné
 * @property int $annee_universitaire_id Année universitaire
 * @property int $salle_id
 * @property \Illuminate\Support\Carbon $jour_examen
 * @property string $epreuve Nom de l'épreuve
 * @property string $code_prefix Préfixe du code (Ex: TA, SA)
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\AnneeUniversitaire $anneeUniversitaire
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CodeAnonymat> $codesAnonymat
 * @property-read int|null $codes_anonymat_count
 * @property-read \App\Models\Niveau $niveau
 * @property-read \App\Models\Salle $salle
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchemaCodage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchemaCodage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchemaCodage onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchemaCodage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchemaCodage whereAnneeUniversitaireId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchemaCodage whereCodePrefix($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchemaCodage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchemaCodage whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchemaCodage whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchemaCodage whereEpreuve($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchemaCodage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchemaCodage whereJourExamen($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchemaCodage whereNiveauId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchemaCodage whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchemaCodage whereSalleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchemaCodage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchemaCodage withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchemaCodage withoutTrashed()
 */
	class SchemaCodage extends \Eloquent {}
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionExam concours()
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

