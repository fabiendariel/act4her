<?php

namespace AppBundle\Helper;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Extension de twig pour y ajouter des fonctions personnalisées
 */
class TwigHelper extends \Twig_Extension
{
    // Container de service
    private $container;

    // Paramètres iReflet issus de app/config/config.yml
    private $ireflet;

    // Raccourcis vers les services
    private $checker;
    private $trans;

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * TwigHelper constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $this->ireflet = $this->container->getParameter('ireflet');
        $this->checker = $this->container->get('security.authorization_checker');
        $this->trans   = $this->container->get('translator');
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Returns the name of the extension.
     * @return string The extension name
     */
    public function getName()
    {
        return 'AppTwigHelper';
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Returns a list of functions to add to the existing list.
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('assetic_version',     [$this, 'asseticVersion']),
            new \Twig_SimpleFunction('var_params',          [$this, 'varParams']),
            new \Twig_SimpleFunction('var_label',           [$this, 'varLabel']),
            new \Twig_SimpleFunction('app_icon',            [$this, 'appIcon']),
            new \Twig_SimpleFunction('get_controller_name', [$this, 'getControllerName']),
            new \Twig_SimpleFunction('isGranted',           [$this, 'isGranted'])
        ];
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Returns a list of filters to add to the existing list.
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('basename', [$this, 'basenameFilter'])
        ];
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Retourne une "Version" à ajouter aux fichiers statiques pour faire du cache busting
     * @return string
     */
    public function asseticVersion()
    {
        if (in_array($this->container->getParameter('kernel.environment'), ['dev', 'test', 'recette']))
        {
            return time();
        }

        if (file_exists('version'))
        {
            return trim(file_get_contents('version'));
        }

        return date('W');
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Injection des configurations symfony pour Javascript
     * @return string
     */
    public function varParams()
    {
        return json_encode([
            'is_authenticated' => $this->checker->isGranted('IS_AUTHENTICATED_REMEMBERED'),
            'ireflet_enabled'  => $this->ireflet['ireflet_enabled'],
            'portal_url'       => $this->ireflet['portal_url'],
            'document_domain'  => $this->ireflet['document_domain'],
            'debug_enabled'    => $this->ireflet['debug_enabled']
        ]);
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Retourne un ensemble de labels à injecter dans les scripts JavaScript
     * @return string Json
     */
    public function varLabel()
    {


        return json_encode([
            // Générique
            'enregistrer'           => $this->trans->trans('Enregistrer'),
            'enregistrement_ok'     => $this->trans->trans('enregistrement_ok'),
            'supprimer'             => $this->trans->trans('Supprimer'),
            'annuler'               => $this->trans->trans('Annuler'),
            'envoyer'               => $this->trans->trans('Envoyer'),
            'update'                => $this->trans->trans('Modifier'),
            'form_field_error'      => $this->trans->trans('Veuillez renseigner les champs en surbrillance'),
            'faq_save_ok'           => $this->trans->trans('faq_save_ok'),
            'statut_change'         => $this->trans->trans('Changement de statut'),
            'statut_change_confirm' => $this->trans->trans('Confirmer le changement de statut'),
            'done'                  => $this->trans->trans('Traiter'),

            // Contact
            'contact'               => $this->trans->trans('Contact'),
            'type_contact'          => $this->trans->trans('Type de contact'),
            'mode'                  => $this->trans->trans('Mode'),
            'hors_faq'              => $this->trans->trans('Hors faq'),
            'faq'                   => $this->trans->trans('Faq'),
            'commentaires'          => $this->trans->trans('Commentaires'),
            'aboutissements'        => $this->trans->trans('Aboutissements'),
            'typologies'            => $this->trans->trans('Typologies'),
            'creer_contact'         => $this->trans->trans('Créer un contact'),
            'programmer_rappel'     => $this->trans->trans('Programmer un rappel'),
            'programmer_formation'  => $this->trans->trans('Programmer une formation'),
            'contact_save_ok'       => $this->trans->trans('contact_save_ok'),
            'date'                  => $this->trans->trans('Date'),
            'typologie'             => $this->trans->trans('typologie'),
            'aboutissement'         => $this->trans->trans('Aboutissement'),
            'utilisateur'      	 	=> $this->trans->trans('Utilisateur'),
            'commentaire'       	=> $this->trans->trans('Commentaire'),
            'search_patient'    	=> $this->trans->trans('Rechercher un patient'),
            'open_patient'      	=> $this->trans->trans('Ouvrir la fiche patient'),
            'infos_contact'     	=> $this->trans->trans('Informations Contact'),
        	'PV_RQP' 				=> $this->trans->trans('PV/RQP'),
        	'PV' 					=> $this->trans->trans('Pharmacovigilance'),
        	'RQP' 					=> $this->trans->trans('Réclamation qualité produit'),
        	'notificateur'			=> $this->trans->trans('Notificateur'),
          'pnt_idet'			=> $this->trans->trans('Veuillez préciser quel est l\'IDET concerné'),
          'pnt_autre'			=> $this->trans->trans('Autre : Précisez'),
          'notificateurs'			=> $this->container->get('app.repository.pharmacovigilance.notificateur.Type')->findAllAsSelectDataSource(),
          'notificateurs_idet'			=> array(),
          'code_cip_produit'		=> $this->trans->trans('Code CIP du produit '),
        	'num_lot_produit'		=> $this->trans->trans('N° de lot du produit'),
          'nom_produit'		=> $this->trans->trans('Produit concerné'),
          'denomination_produit'		=> $this->trans->trans('Dénomination'),
          'qte_produit'		=> $this->trans->trans('Quantité concernée'),
          'dispo_produit'		=> $this->trans->trans('Est-ce que le produit est disponible pour rapatriement vers le fabricant ?'),
          'photo_produit'		=> $this->trans->trans('Est-ce que des photos du défaut qualité sont disponibles ?'),
          'date_peremption'		=> $this->trans->trans('Date de péremption'),
          'date_reception'		=> $this->trans->trans('Date de réception du signalement par Patientys'),
          'date_decouverte'		=> $this->trans->trans('Date de découverte de l\'anomalie par le notificateur'),
          'nature_evenement'	=> $this->trans->trans('Nature de la réclamation'),
          'description_evenement'	=> $this->trans->trans('Description de l\'événement indésirable / réclamation produit'),
	        'cas_grave'				=> $this->trans->trans('Cas grave'),
        	'oui'					=> $this->trans->trans('Oui'),
        	'non'					=> $this->trans->trans('Non'),
        	'info_complement' 		=> $this->trans->trans('Informations complémentaires (antécédents, traitements concomittants, pathologies concomittantes...)'),
	        'declarant' 			=> $this->trans->trans('Déclarant'),
        	'declarants'			=> $this->container->get('app.repository.pharmacovigilance.declarant.Type')->findAllAsSelectDataSource(),
          'pv_rqv_confirm'		=> $this->trans->trans("Confirmez-vous l'enregistrement et l'envoi de la fiche de PV/RQP?"),
       		'incident_save_ok'    	=> $this->trans->trans('Informations enregistrées!'),
        	'pharmacovigilance_types'=> $this->container->getParameter("pharmacovigilance")['type'],
          'notificateur_autre_val'=> $this->container->getParameter('pharmacovigilance')['notificateurTypes']['autre'],
          'confirmer'				=> $this->trans->trans('Confirmer'),

            // Programmation d'une formation
          'date_formation'       => $this->trans->trans('Date de la formation'),
          'plage_horaire'     => $this->trans->trans('Plage horaire'),
          'heure_formation'      => $this->trans->trans('Heure de la formation'),
          'minute_formation'     => $this->trans->trans('Minute de la formation'),
          'formation_error_date' => $this->trans->trans('Veuillez renseigner une date.'),
          'formation_error_hour' => $this->trans->trans("L'heure de la formation est en dehors de la plage horaire sélectionnée ."),
          'formation_save_ok'    => $this->trans->trans('formation_save_ok'),
            
            // Document
            'delete_document'          => $this->trans->trans('Suppression de document'),
            'delete_document_confirm'  => $this->trans->trans('Voulez-vous vraiment supprimer le document $$doc_name$$ ?'),
            'document_sended'          => $this->trans->trans('document_sended'),
            'document_save_ok'         => $this->trans->trans('document_save_ok'),
            'transmission_popup_title' => $this->trans->trans('Transmission de document'),
            'destinataire'             => $this->trans->trans('Destinataire'),
            'adresse'                  => $this->trans->trans('Adresse'),
            'message'                  => $this->trans->trans('Message'),
            'canal'                    => $this->trans->trans("Canal d'envoi"),

            // Professionnels rattachés au patient
            'nom'                          => $this->trans->trans('Nom'),
            'prenom'                       => $this->trans->trans('Prénom'),
            'type'                         => $this->trans->trans('Type'),
            'adresseur'                    => $this->trans->trans('Adresseur'),
            'traitant'                     => $this->trans->trans('Traitant'),
            'terrain'                      => $this->trans->trans('Terrain'),
            'no_professionnels'            => $this->trans->trans("Aucun professionnel n'est rattaché à ce patient."),
            'delete_professionnel'         => $this->trans->trans("Suppression de rattachement d'un professionnel"),
            'delete_professionnel_confirm' => $this->trans->trans('Voulez-vous vraiment supprimer ce rattachement ?'),
			
        	// Professionnels rattachés au service
        	'no_professionnels_service'            => $this->trans->trans("Aucun professionnel n'est rattaché à ce service."),
        	'no_service_selected'            => $this->trans->trans("Veuillez selectionner un service!"),
        		
            // Patient
            'patient_no_results'   => $this->trans->trans('Aucun patient ne correspond à vos critères de recherche.<br />Voulez-vous le créer ?<br /><br />'),
            'create_professionnel' => $this->trans->trans('Créer un professionnel'),
            'create_patient'       => $this->trans->trans('Créer ce patient'),

            // Professionnel
            'professionnel_no_results' => $this->trans->trans('Aucun professionnel ne correspond à vos critères de recherche.<br />Voulez-vous le créer ?'),
	
            // Questionnaire
            'open_survey' => $this->trans->trans('Ouvrir le questionnaire'),

            // Programmation du rappel
            'date_rappel'       => $this->trans->trans('Date du rappel'),
            'plage_horaire'     => $this->trans->trans('Plage horaire'),
            'heure_rappel'      => $this->trans->trans('Heure du rappel'),
            'minute_rappel'     => $this->trans->trans('Minute du rappel'),
            'rappel_error_date' => $this->trans->trans('Veuillez renseigner une date.'),
            'rappel_error_hour' => $this->trans->trans("L'heure de rappel est en dehors de la plage horaire sélectionnée ."),
            'rappel_save_ok'    => $this->trans->trans('rappel_save_ok'),

            // Cure
            'programmer_cure' => $this->trans->trans('Créer un dossier de cure'),
            'date_cure'       => $this->trans->trans('Date de la cure'),
            'num_sejour_cure' => $this->trans->trans('Numéro de séjour'),
            'heure_cure'      => $this->trans->trans('Heure du RDV'),
            'minute_cure'     => $this->trans->trans('Minute du RDV'),
            'heure_rappel_cure'      => $this->trans->trans('Heure du rappel'),
            'minute_rappel_cure'     => $this->trans->trans('Minute du rappel'),
            'set_rappel_cure'        => $this->trans->trans('Programmer le rappel J-2'),
            'add_suivi_title' => $this->trans->trans('Ajouter un suivi de cure'),
            'add_suivi'       => $this->trans->trans('Ajouter un suivi'),
            'cures'       => $this->trans->trans('Cures'),

            // Rappel
            'aucun_courrier' => $this->trans->trans('Aucun courrier à envoyer'),
            'aucun_document' => $this->trans->trans('Aucun document à envoyer'),

            // Main
            'app_name'                => $this->trans->trans('Nom'),
            'app_desc'                => $this->trans->trans('Description'),
            'app_save'                => $this->trans->trans('Enregistrer'),
            'app_delete'              => $this->trans->trans('Supprimer'),
            'app_edit'                => $this->trans->trans('Éditer'),
            'app_cancel'              => $this->trans->trans('Annuler'),
            'app_drama_error'         => $this->trans->trans("Erreur système. Veuillez contacter l'administrateur de l'application."),

            // Permission
            'app_perm_edit'           => $this->trans->trans('Éditer une permission'),
            'app_perm_add'            => $this->trans->trans('Ajouter une permission'),
            'app_perm_delete'         => $this->trans->trans('Supprimer une permission'),
            'app_perm_delete_confirm' => $this->trans->trans('Voulez-vous vraiment supprimer la permission %permission_label% ?'),
            'app_perm_label_error'    => $this->trans->trans('Veuillez saisir un nom pour cette permission'),

            // Profil
            'app_profil_delete_confirm' => $this->trans->trans('Voulez-vous vraiment supprimer le profil %profil_label% ?'),
            'app_profil_delete'         => $this->trans->trans('Supprimer un profil'),
            'app_profil_select'         => $this->trans->trans('Choisissez un profil'),

            // Concurrence d'accès
            'app_access_user'  => $this->trans->transChoice('Utilisateur présent', 1),
            'app_access_users' => $this->trans->transChoice('Utilisateur présent', 2),

            'send_contrat_idet_ok' => $this->trans->trans('L\'envoi du contrat à l\'IDE Terrain est effectif'),
        ]);
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @param $icon
     * @return string
     */
    public function appIcon($icon)
    {
        if (file_exists('images/applications/' . strtolower($icon) . '.png'))
        {
            return '<img src="/images/applications/' . strtolower($icon) . '.png" width="20" height="20">';
        }

        return '<span class="glyphicon glyphicon-picture"></span>';
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @param $request_attributes
     * @return mixed
     */
    public function getControllerName(ParameterBag $request_attributes)
    {
        $matches = [];

        preg_match('/Controller\\\\([a-zA-Z]*)Controller/', $request_attributes->get('_controller'), $matches);

        return $matches[1];
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @param $role
     * @return bool
     */
    public function isGranted($role)
    {
        return $this->container->get('security.service.role')->isGranted($role);
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @var string $value
     * @var string $suffix
     * @return string
     */
    public function basenameFilter($value, $suffix = '')
    {
        return basename($value, $suffix);
    }
}
