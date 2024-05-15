<?php

namespace AppBundle\Helper;

use AppBundle\Entity\Action;
use AppBundle\Entity\Contact;
use AppBundle\Entity\Patient;
use AppBundle\Entity\Document;
use AppBundle\Entity\Professionnel;
use AppBundle\Entity\Portage;
use AppBundle\Entity\ServiceProfessionnel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;

class GenericHelper
{
    // Container de service
    private $container;

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Retourne un tableau formaté des contacts pour retour Json dans les requêtes Ajax
     * @param Contact|null $contact
     * @return array
     */
    public function formatContact( $contact = null)
    {
        if (null !== $contact)
        {
            $faq_ids = [];

            if (null !== $contact->getContactFaqs())
            {
                foreach ($contact->getContactFaqs() as $faq)
                {
                    $faq_ids[] = $faq->getId();
                }
            }

            $precure = 0;
            $questionnaire = 0;
            if($contact->getCure() && count($contact->getCure()->getCureQuestionnaires()) > 0){
                $questionnaire =  $contact->getCure()->getCureQuestionnaires()[0]->getId();
                if($contact->getCure()->getCureQuestionnaires()[0]->getQuestionnaireDocument())
                    $precure = 1;
            }

            return [
              'contact_id'       => $contact->getId(),
              'professionnel_id' => null !== $contact->getProfessionnel()        ? $contact->getProfessionnel()->getId()        : null,
              'patient_id'       => null !== $contact->getPatient()              ? $contact->getPatient()->getId()              : null,
              'mode_id'          => null !== $contact->getContactMode()          ? $contact->getContactMode()->getId()          : null,
              'type_id'          => null !== $contact->getContactType()          ? $contact->getContactType()->getId()          : null,
              'typologie_id'     => null !== $contact->getContactTypologie()     ? $contact->getContactTypologie()->getId()     : null,
              'aboutissement_id' => null !== $contact->getContactAboutissement() ? $contact->getContactAboutissement()->getId() : null,
              'cure_id'          => null !== $contact->getCure()                 ? $contact->getCure()->getId() : null,
              'precure'          => $precure,
              'questionnaire'    => $questionnaire,
              'deletable'        => 0 === count($contact->getActions()),
              'hors_faq'         => $contact->getHorsFaq(),
              'commentaire'      => $contact->getCommentaire(),
              'faq_ids'          => $faq_ids
            ];
        }

        return [];
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Contrôle d'unicité du nom d'un fichier et ajout d'un index si le fichier n'est pas unique
     * @param     $filename
     * @param     $directory
     * @param int $index
     * @return string
     */
    public function getUniqueFileName($filename, $directory, $index = 0)
    {
        $fileinfo  = new \SplFileInfo($filename);
        $extension = $fileinfo->getExtension();

        $filename = $this->sanitize_filename(basename($filename, '.' . $extension)) . $extension;

        $tmpfilename = $filename;

        // Cas des passes récursives
        if (0 !== $index)
        {
            $tmpfilename = basename($filename, '.' . $extension) . '-' . $index . '.' . $extension;
        }

        $fs = new Filesystem;

        // Si le fichier existe déjà
        if ($fs->exists($directory . '/' . $tmpfilename))
        {
            $tmpfilename = $this->getUniqueFileName($filename, $directory, ++$index);
        }

        return $tmpfilename;
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Formatage du nom du document
     * @param $fileName
     * @return string
     */
    public function sanitize_filename($fileName)
    {
        $fileInfo  = new \SplFileInfo($fileName);
        $extension = $fileInfo->getExtension();

        $tmpFileName = basename($fileName, '.' . $extension);

        $newName = trim($tmpFileName);
        $newName = htmlentities($newName, ENT_NOQUOTES, 'utf-8');
        $newName = preg_replace('#&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);#i', '\\1', $newName);
        $newName = preg_replace('#[^a-z0-9]+#i', '_', $newName);

        return $newName . '.' . $extension;
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Formatage d'un label
     * @param $string
     * @return string
     */
    public function sanitize_label($string)
    {
        $string = trim($string);
        $string = htmlentities($string, ENT_NOQUOTES, 'utf-8');
        $string = preg_replace('#&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);#i', '\\1', $string);
        $string = preg_replace('#[^a-z]+#i', '_', $string);

        return strtoupper($string);
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Persistance en session des données de recherche du patient
     * @param Request $request
     * @return Patient
     */
    public function setPatientSearch(Request $request)
    {
        $patient = new Patient;

        // Enregistrement en session des paramètres de recherche
        if (null !== $request->get('patient_search'))
        {
            $request->getSession()->set('patient_search_datas', $request->get('patient_search'));
        }
        else
        {
            if (null !== $search_engine_datas = $request->getSession()->get('patient_search_datas'))
            {
                $patient->setNom($search_engine_datas['nom']);
                $patient->setPrenom($search_engine_datas['prenom']);
                $patient->setCodePostal($search_engine_datas['codePostal']);
                $patient->setIdentification($search_engine_datas['identification']);
                $patient->setId($search_engine_datas['id']);
                $patient->setTelephoneFixe($search_engine_datas['telephoneFixe']);
            }
        }

        return $patient;
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Persistance en session des données de recherche du professionnel
     * @param Request $request
     * @return Professionnel
     */
    public function setProfessionnelSearch(Request $request)
    {
        // Objet standard pour la création du formulaire
        $professionnel = new Professionnel;

        // Enregistrement en session des paramètres de recherche
        if (null !== $request->get('professionnel_search'))
        {
            $request->getSession()->set('professionnel_search_datas', $request->get('professionnel_search'));
        }
        else
        {
            if (null !== $search_engine_datas = $request->getSession()->get('professionnel_search_datas'))
            {
                $professionnel->setNom($search_engine_datas['nom']);
                $professionnel->setPrenom($search_engine_datas['prenom']);
                $professionnel->setCodePostal($search_engine_datas['codePostal']);
                $professionnel->setId($search_engine_datas['id']);
                $professionnel->setTelephoneFixe($search_engine_datas['telephoneFixe']);
                $professionnel->setEmailEnabled(true);
                $professionnel->setEnvoiCr(true);

                if (null !== $type = $this->container->get('app.repository.professionnel.type')->find($search_engine_datas['type']))
                {
                    $professionnel->setType($type);
                }
            }
        }

        return $professionnel;
    }

    /**
     * Persistance en session des données de recherche du professionnel
     * @param Request $request
     * @return Professionnel
     */
    public function setAnnuaireSearch(Request $request)
    {
        // Objet standard pour la création du formulaire
        $serviceProfessionnel = new ServiceProfessionnel;

        // Enregistrement en session des paramètres de recherche
        if (null !== $request->get('service_professionnel_search'))
        {
            $request->getSession()->set('service_professionnel_search_datas', $request->get('service_professionnel_search'));
        }
        else
        {
            if (null !== $search_engine_datas = $request->getSession()->get('service_professionnel_search_datas'))
            {
                //$serviceProfessionnel->setCodePostal($search_engine_datas['codePostal']);

            }
        }

        return $serviceProfessionnel;
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @param $timestamp
     * @return string
     */
    public function getDateFr($timestamp)
    {
        setlocale(LC_ALL, 'fr_FR.utf8', 'fra');
        $dateFr = ucfirst(strftime('%A<br />%d/%m/%G', $timestamp));
        setlocale (LC_ALL, null);

        return $dateFr;
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @param $timestamp
     * @return string
     */
    public function getDayFr($timestamp)
    {
        setlocale(LC_ALL, 'fr_FR.utf8', 'fra');
        $dateFr = ucfirst(strftime('%A', $timestamp));
        setlocale (LC_ALL, null);

        return $dateFr;
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Formate un numéro de téléphone pour l'afficher avec des espaces
     * @param $num
     * @return string
     */
    public function displayPhoneNumber($num)
    {
        return preg_replace('/(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/','\1 \2 \3 \4 \5', $num);
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @return bool
     */
    function check_node_server()
    {
        $url = $this->container->getParameter('node')['scheme'] . '://' .
          $this->container->getParameter('node')['host'] . '/' .
          $this->container->getParameter('node')['namespace'] . '/socket.io/socket.io.js';

        $handler = curl_init();

        curl_setopt($handler, CURLOPT_URL,            $url);
        curl_setopt($handler, CURLOPT_TIMEOUT,        10);
        curl_setopt($handler, CURLOPT_HEADER,         true);
        curl_setopt($handler, CURLOPT_NOBODY,         true);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handler, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($handler, CURLOPT_FAILONERROR,    1);

        curl_exec($handler);

        $http_code = curl_getinfo($handler, CURLINFO_HTTP_CODE);

        curl_close($handler);

        return (200 === $http_code || 302 === $http_code);
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Bouchonnage en attendant une méthode de récupération du comportement des questions liées
     * @param $questionnaire_id
     * @return string
     * @TODO : Implémenter un système pour gérer les paramètres des questions liées
     */
    public function getQuestionnaireToogle($questionnaire_id)
    {
        $toogle = [];
        
            $toogle = [
              ['item' => 'q5',  'check' => ['Autre'],  'parse' => ['tr_q5_autre']],
              ['item' => 'q7',  'check' => ['Oui'],  'parse' => ['tr_q8']],
              ['item' => 'q15',  'check' => ['Oui'],  'parse' => ['tr_q16']],
            ];


        return json_encode($toogle);
        /*$entityManager = $this
          ->container
          ->get('doctrine.orm.entity_manager');

        $toggle_liste = $this
          ->container
          ->get('app.repository.questionnaire.toggle')
          ->findByQusId($questionnaire_id);
        $toggle = [];
        $parse = [];

        foreach($toggle_liste as $item){
            $parse[$item['question']][$item['check']]['parse'][] = $item['parse'];
        }
        foreach($toggle_liste as $item2){
            $toggle[] = ['item' => $item2['question'],  'check' => $item2['check'],  'parse' => $parse[$item2['question']][$item2['check']]['parse']];
        }

        return json_encode($toggle);*/
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Création de l'action si elle n'existe pas déjà à l'enregistrement d'un bilan de suivi
     * @param $cure_questionnaire
     * @param $user
     */
    public function setQuestionnaireCureAction($cure_questionnaire, $user)
    {
        if (null === $cure_questionnaire->getAction())
        {
            $entityManager = $this
              ->container
              ->get('doctrine.orm.entity_manager');

            $action = new Action;

            $action->setPatient(
              $this
                ->container
                ->get('app.repository.patient')
                ->find($cure_questionnaire->getAction()->getPatient()->getId())
            );

            // Si l'utilisateur est connecté
            if (null !== $user)
            {
                $action->setUser($user);
            }
            // Si le patient a un compte utilisateur
            else if (null !== $cure_questionnaire->getAction()->getPatient()->getUser())
            {
                $action->setUser($cure_questionnaire->getAction()->getPatient()->getUser());
            }
            // Autrement on va chercher un compte patient anonyme
            else
            {
                $action->setUser(
                  $this
                    ->container
                    ->get('app.repository.user')
                    ->find(
                      $this
                        ->container
                        ->getParameter('users_id')['patient_anonyme']
                    )
                );
            }

            $action->addBilan($cure_questionnaire);

            // Type d'action
            $action->setActionType(
              $this
                ->container
                ->get('app.repository.action.type')
                ->find($this->container->getParameter('action_type')['questionnaire_de_suivi'])
            );

            // Statut de l'action
            $action->setActionStatut(
              $this
                ->container
                ->get('app.repository.action.statut')
                ->find($this->container->getParameter('action_statut')['en_cours'])
            );

            $action->setDateCreation(new \DateTime);

            $entityManager->persist($action);

            $cure_questionnaire->setAction($action);
            $entityManager->flush();
        }
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Création de l'action si elle n'existe pas déjà à l'enregistrement d'un bilan de suivi
     * @param $cure_questionnaire
     * @param $user
     */
    public function setQuestionnaireAction($cure_questionnaire, $user, $action_id, $action_type_id, $patient_id)
    {

        if (null === $cure_questionnaire->getAction() && null === $action_id)
        {
            $entityManager = $this
              ->container
              ->get('doctrine.orm.entity_manager');

            $action = new Action;

            $action->setPatient( $this
              ->container
              ->get('app.repository.patient')
              ->find($patient_id));

            // Si l'utilisateur est connecté
            if (null !== $user)
            {
                $action->setUser($user);
            }
            else
            {
                $action->setUser(
                  $this
                    ->container
                    ->get('app.repository.user')
                    ->find(
                      $this
                        ->container
                        ->getParameter('users_id')['patient_anonyme']
                    )
                );
            }

            $action->addBilan($cure_questionnaire);

            // Type d'action
            $action->setActionType(
              $this
                ->container
                ->get('app.repository.action.type')
                ->find($action_type_id)
            );

            // Statut de l'action
            $action->setActionStatut(
              $this
                ->container
                ->get('app.repository.action.statut')
                ->find($this->container->getParameter('action_statut')['en_cours'])
            );

            $action->setDateCreation(new \DateTime);

            /*if($action_type_id->getId() == $this->container->getParameter('action_type')['rappel_premiere_injection']){
                $patient = $this->container
                  ->get('app.repository.patient')
                  ->find($patient_id);
                if($patient)
                    $action->setDateEcheance($patient->getDateInjection());
                else
                    $action->setDateEcheance(new \DateTime);
            }*/

            $entityManager->persist($action);
            $entityManager->flush();

            $cure_questionnaire->setAction($action);
            $entityManager->flush();
        }elseif(null === $cure_questionnaire->getAction()){
            $entityManager = $this
              ->container
              ->get('doctrine.orm.entity_manager');

            $action = $this->container
              ->get('app.repository.action')
              ->find($action_id);
            /*if($action && $action_type_id->getId() == $this->container->getParameter('action_type')['premiere_injection']){
                $patient = $this->container
                  ->get('app.repository.patient')
                  ->find($patient_id);
                if($patient)
                    $action->setDateEcheance($patient->getDateInjection());
                else
                    $action->setDateEcheance(new \DateTime);
            }*/

            $cure_questionnaire->setAction($this->container->get('app.repository.action')->find($action_id));
            $entityManager->flush();
        }else{
            $action = $this->container
              ->get('app.repository.action')
              ->find($action_id);
            /*if($action && $action_type_id->getId() == $this->container->getParameter('action_type')['premiere_injection']){
                $patient = $this->container
                  ->get('app.repository.patient')
                  ->find($patient_id);
                if($patient)
                    $action->setDateEcheance($patient->getDateInjection());
                else
                    $action->setDateEcheance(new \DateTime);
            }*/
        }
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Création d'une action
     * @param $patient
     * @param $professionnel
     * @param $user
     * @param $cure
     * @param $type
     * @param $statut
     * @param $dateCreation
     */
    public function setActionGenerique($patient, $professionnel, $user, $cure, $type, $statut, $dateCreation, $dateEcheance = null, $dateExecution = null, $parent = null)
    {
        $entityManager = $this
          ->container
          ->get('doctrine.orm.entity_manager');

        $action = new Action;

        if($patient != null)
            $action->setPatient($patient);

        if($professionnel != null)
            $action->setProfessionnel($professionnel);

        if($user != null)
            $action->setUser($user);

        if($cure != null)
            $action->setCure($cure);

        $action->setActionType($type);

        $action->setActionStatut($statut);

        $action->setDateCreation(new \DateTime);

        if($dateEcheance != null){
            $action->setDateEcheance($dateEcheance);
        }

        if($dateExecution != null)
            $action->setDateExecution($dateExecution);

        if($parent != null)
            $action->setParent($parent);


        $entityManager->persist($action);
        $entityManager->flush();

        /*
         * Création du rappel/action
        */
        if($dateEcheance != null && !in_array($type->getId(),$this->container->getParameter("action_type")["liste_injection"])) {
            $request = new Request;
            $rappel_date_base = new \DateTime($dateEcheance->format('Y-m-d'));
            $rappel_date_base->format('Y-m-d');
            $rappel_date = $this
              ->container
              ->get('app.date.helper')
              ->getNextAvailableDay($rappel_date_base)
              ->getTimestamp();
            // Plage horaire par défaut
            $plage_horaire = $this->container->get('app.repository.plage.horaire')->findAll()[0];
            $plage_horaire_id = $plage_horaire->getId();
            $plage_horaire_heure = $plage_horaire->getDebut();

            if($patient != null){
                // Recherche d'une préférence patient correspondant à la date effective de rappel
                $patientPreferenceHoraires = $patient->getPreferenceHoraires();
                foreach ($patientPreferenceHoraires as $patientPreferenceHoraire) {
                    if (
                      method_exists($patientPreferenceHoraire, 'get' . $rappel_date) &&
                      true === $patientPreferenceHoraire->{'get' . $rappel_date}()
                    ) {
                        $plage_horaire_id = $patientPreferenceHoraire->getPreferencePlageHoraire()->getId();
                        $plage_horaire_heure = $patientPreferenceHoraire->getPreferencePlageHoraire()->getDebut();
                    }
                }
                $request->attributes->set('patient_id', $patient->getId());
            }else{
                // Recherche d'une préférence patient correspondant à la date effective de rappel
                $proPreferenceHoraires = $professionnel->getPreferenceHoraires();
                foreach ($proPreferenceHoraires as $proPreferenceHoraire) {
                    if (
                      method_exists($proPreferenceHoraire, 'get' . $rappel_date) &&
                      true === $proPreferenceHoraire->{'get' . $rappel_date}()
                    ) {
                        $plage_horaire_id = $proPreferenceHoraire->getPreferencePlageHoraire()->getId();
                        $plage_horaire_heure = $proPreferenceHoraire->getPreferencePlageHoraire()->getDebut();
                    }
                }
                $request->attributes->set('professionnel_id', $professionnel->getId());
            }
            $request->attributes->set('plage_horaire_id', $plage_horaire_id);
            $request->attributes->set('rappel_heure', $plage_horaire_heure->format('H'));
            $request->attributes->set('rappel_minute', $plage_horaire_heure->format('i'));
            $request->attributes->set('rappel_fixe', false);

            // Surcharge de l'objet Request
            $request->attributes->set('rappel_date', $rappel_date);
            $this->container->get('app.manager.rappel')->createRappel($request, null, $action->getActionType(),null, true, $action);
        }



        return $action;
    }

    /**
     * Ajoute le contenu dynamique dans un texte
     */
    public function addDynamicText($texte, $patient, $cible, $date_message = null){

        $ajd = new \DateTime();
        if($date_message == null)
            $date_message = $ajd;

        $destination = $this->container->getParameter('hosts')['extranet'];

        $tab_key_exp = array(
          array('source' => '&laquo;T_Cible_1nom&raquo;',
            'cible'  => $cible->getNom()),
          array('source' => '&laquo;T_Cible_1prenom&raquo;',
            'cible'  => $cible->getPrenom()),
          array('source' => '&laquo;T_AdresseAdresse&raquo;',
            'cible'  => $cible->getAdresse()),
          array('source' => '&laquo;Adresse2&raquo;',
            'cible'  => $cible->getAdresseComplementaire()),
          array('source' => '&laquo;T_AdresseCode_postal&raquo;',
            'cible'  => $cible->getCodePostal()),
          array('source' => '&laquo;T_AdresseVille&raquo;',
            'cible'  => $cible->getVille()),
          array('source' => '[DATE]',
            'cible'  => $ajd->format('d/m/Y')),
          array('source' => '[DATE_MESSAGE]',
            'cible'  => $date_message->format('d/m/Y')),
          array('source' => '[LIEN_EXTRANET]',
            'cible'  => 'http://'.$destination),
        );

        if($patient != null){
            $civilite = $patient->getCivilite() != null ? $patient->getCivilite()->getLabel().' ' : '';
            $dateConsentement = $ajd;
            $dateNonConsentement = $ajd;
            $dateNextInjection = $ajd;
            $action = $this->container->get('app.repository.action')->findLastInjection($patient->getId(),false);
            if($action){
                $dateNextInjection =  $action->getDateEcheance();
            }

            // Récupération d'ajout de fichier de consentement
            foreach($patient->getDocuments() as $document){
                if($document->getDocumentType()->getId() == $this->container->getParameter('documents')['type']['consentement_patient']){
                    $dateConsentement = $document->getDateCreation();
                }
                if($document->getDocumentType()->getId() == $this->container->getParameter('documents')['type']['consentement_non_signe']
                  || $document->getDocumentType()->getId() == $this->container->getParameter('documents')['type']['consentement_incomplet']){
                    $dateNonConsentement = $document->getDateCreation();
                }
            }


            $tab_key_exp = array_merge($tab_key_exp, array(
              array('source' => '[PRENOM_PATIENT]',
                'cible' =>  $patient->getPrenom()),
              array('source' => '[NOM_PATIENT]',
                'cible' =>  $patient->getNom()),
              array('source' => '[ID_PATIENT]',
                'cible' =>  $patient->getId()),
              array('source' => '[INITIALE_PATIENT]',
                'cible' =>  strtoupper(mb_substr($patient->getPrenom(), 0, 1) . mb_substr($patient->getNom(), 0, 3))),
              array('source' => '[NOM_TRAITEMENT]',
                'cible' =>  $patient->getTraitement() == null ? 'Traitement non mentionné' : $patient->getTraitement()->getLabel() ),
              array('source' => '[CIVILITE_PATIENT]',
                'cible' =>  $civilite),
              array('source' => '[NOM_COMPLET_PATIENT]',
                'cible' =>  $patient->getPrenom().$patient->getNom()),
              array('source' => '[DATE_CONSENTEMENT]',
                'cible' =>  $dateConsentement?$dateConsentement->format('d/m/Y'):''),
              array('source' => '[DATE_NON_CONSENTEMENT]',
                'cible' =>  $dateNonConsentement?$dateNonConsentement->format('d/m/Y'):''),
              array('source' => '[DATE_NEXT_INJECTION]',
                'cible' =>  $dateNextInjection?$dateNextInjection->format('d/m/Y'):''),
            ));

            $action_idet = $this->container->get('app.repository.action')->findOneBy(['actionType' => $this->container->get('app.repository.action.type')->find(84), 'patient' => $patient]);
            $dateFormation = 'Aucune date';
            if($action_idet){
                foreach($action_idet->getBilans() as $bilan){
                    foreach($bilan->getQuestionnaireResultat()->getReponses() as $reponse){
                        if($reponse->getQuestion()->getId() == 54 && $reponse->getValeurTexte() != null && $reponse->getValeurTexte() != ''){
                            $dateFormation = $reponse->getValeurTexte();
                        }
                    }
                }
                $tab_key_exp = array_merge($tab_key_exp, array(
                    array('source' => '[DATE_FORMATION]',
                        'cible' =>  $dateFormation),
                ));
            }

        }


        if($patient != null) {
            if ($cible != null) {
                $civilite_patprs = $cible->getCivilite() != null ? $cible->getCivilite()->getLabel() . ' ' : '';
                $tab_key_exp = array_merge($tab_key_exp, array(
                  array('source' => '[NOM_COMPLET_PRESCRIPTEUR]',
                    'cible' => $cible->getPrenom() . ' ' . $cible->getNom()),
                  array('source' => '[NOM_PRESCRIPTEUR]',
                    'cible' => $cible->getNom()),
                  array('source' => '[PRENOM_PRESCRIPTEUR]',
                    'cible' => $cible->getPrenom()),
                  array('source' => '[TELEPHONE_PRESCRIPTEUR]',
                    'cible' => $cible->getTelephoneFixe()),
                  array('source' => '[TELEPHONE_MOBILE_PRESCRIPTEUR]',
                    'cible' => $cible->getTelephoneMobile()),
                  array('source' => '[CIVILITE_PHARMACIEN]',
                    'cible' => $civilite_patprs),
                  array('source' => '[NOM_PHARMACIEN]',
                    'cible' => $cible->getNom()),
                  array('source' => '[PRENOM_PHARMACIEN]',
                    'cible' => $cible->getPrenom()),
                  array('source' => '[TELEPHONE_PHARMACIEN]',
                    'cible' => $cible->getTelephoneFixe()),
                  array('source' => '[TELEPHONE_MOBILE_PHARMACIEN]',
                    'cible' => $cible->getTelephoneMobile()),
                  array('source' => '[CIVILITE_LABORATOIRE]',
                    'cible' => $civilite_patprs),
                  array('source' => '[NOM_LABORATOIRE]',
                    'cible' => $cible->getNom()),
                  array('source' => '[PRENOM_LABORATOIRE]',
                    'cible' => $cible->getPrenom()),
                  array('source' => '[TELEPHONE_LABORATOIRE]',
                    'cible' => $cible->getTelephoneFixe()),
                  array('source' => '[TELEPHONE_MOBILE_LABORATOIRE]',
                    'cible' => $cible->getTelephoneMobile()),
                  array('source' => '[ADRESSE_LABORATOIRE]',
                    'cible' => $cible->getAdresse() . ', ' . $cible->getCodePostal() . ' ' . $cible->getVille()),
                  array('source' => '[CIVILITE_IDET]',
                    'cible' => $civilite_patprs),
                  array('source' => '[NOM_IDET]',
                    'cible' => $cible->getNom()),
                  array('source' => '[PRENOM_IDET]',
                    'cible' => $cible->getPrenom()),
                  array('source' => '[TELEPHONE_IDET]',
                    'cible' => $cible->getTelephoneFixe()),
                  array('source' => '[TELEPHONE_MOBILE_IDET]',
                    'cible' => $cible->getTelephoneMobile()),
                  array('source' => '[CIVILITE_TRAITANT]',
                    'cible' => $civilite_patprs),
                  array('source' => '[NOM_TRAITANT]',
                    'cible' => $cible->getNom()),
                  array('source' => '[PRENOM_TRAITANT]',
                    'cible' => $cible->getPrenom()),
                  array('source' => '[TELEPHONE_TRAITANT]',
                    'cible' => $cible->getTelephoneFixe()),
                  array('source' => '[TELEPHONE_MOBILE_TRAITANT]',
                    'cible' => $cible->getTelephoneMobile()),
                ));
            } else {
                foreach ($cible->getPatientProfessionnel() as $patprs) {
                    $civilite_patprs = $patprs->getProfessionnel()->getCivilite() != null ? $patprs->getProfessionnel()->getCivilite()->getLabel() . ' ' : '';

                    if ($patprs->getAdresseur()) {
                        $tab_key_exp = array_merge($tab_key_exp, array(
                          array('source' => '[NOM_COMPLET_PRESCRIPTEUR]',
                            'cible' => $patprs->getProfessionnel()->getPrenom() . ' ' . $patprs->getProfessionnel()->getNom()),
                          array('source' => '[NOM_PRESCRIPTEUR]',
                            'cible' => $patprs->getProfessionnel()->getNom()),
                          array('source' => '[PRENOM_PRESCRIPTEUR]',
                            'cible' => $patprs->getProfessionnel()->getPrenom()),
                          array('source' => '[TELEPHONE_PRESCRIPTEUR]',
                            'cible' => $patprs->getProfessionnel()->getTelephoneFixe()),
                          array('source' => '[TELEPHONE_MOBILE_PRESCRIPTEUR]',
                            'cible' => $patprs->getProfessionnel()->getTelephoneMobile()),
                        ));
                    }

                    if ($patprs->getPharmacien()) {
                        $tab_key_exp = array_merge($tab_key_exp, array(
                          array('source' => '[CIVILITE_PHARMACIEN]',
                            'cible' => $civilite_patprs),
                          array('source' => '[NOM_PHARMACIEN]',
                            'cible' => $patprs->getProfessionnel()->getNom()),
                          array('source' => '[PRENOM_PHARMACIEN]',
                            'cible' => $patprs->getProfessionnel()->getPrenom()),
                          array('source' => '[TELEPHONE_PHARMACIEN]',
                            'cible' => $patprs->getProfessionnel()->getTelephoneFixe()),
                          array('source' => '[TELEPHONE_MOBILE_PHARMACIEN]',
                            'cible' => $patprs->getProfessionnel()->getTelephoneMobile()),
                        ));
                    }

                    if ($patprs->getLaboratoire()) {
                        $tab_key_exp = array_merge($tab_key_exp, array(
                          array('source' => '[CIVILITE_LABORATOIRE]',
                            'cible' => $civilite_patprs),
                          array('source' => '[NOM_LABORATOIRE]',
                            'cible' => $patprs->getProfessionnel()->getNom()),
                          array('source' => '[PRENOM_LABORATOIRE]',
                            'cible' => $patprs->getProfessionnel()->getPrenom()),
                          array('source' => '[TELEPHONE_LABORATOIRE]',
                            'cible' => $patprs->getProfessionnel()->getTelephoneFixe()),
                          array('source' => '[TELEPHONE_MOBILE_LABORATOIRE]',
                            'cible' => $patprs->getProfessionnel()->getTelephoneMobile()),
                          array('source' => '[ADRESSE_LABORATOIRE]',
                            'cible' => $patprs->getProfessionnel()->getAdresse() . ', ' . $patprs->getProfessionnel()->getCodePostal() . ' ' . $patprs->getProfessionnel()->getVille()),
                        ));
                    }

                    if ($patprs->getTerrain()) {
                        $tab_key_exp = array_merge($tab_key_exp, array(
                          array('source' => '[CIVILITE_IDET]',
                            'cible' => $civilite_patprs),
                          array('source' => '[NOM_IDET]',
                            'cible' => $patprs->getProfessionnel()->getNom()),
                          array('source' => '[PRENOM_IDET]',
                            'cible' => $patprs->getProfessionnel()->getPrenom()),
                          array('source' => '[TELEPHONE_IDET]',
                            'cible' => $patprs->getProfessionnel()->getTelephoneFixe()),
                          array('source' => '[TELEPHONE_MOBILE_IDET]',
                            'cible' => $patprs->getProfessionnel()->getTelephoneMobile()),
                        ));
                    }

                    if ($patprs->getTraitant()) {
                        $tab_key_exp = array_merge($tab_key_exp, array(
                          array('source' => '[CIVILITE_TRAITANT]',
                            'cible' => $civilite_patprs),
                          array('source' => '[NOM_TRAITANT]',
                            'cible' => $patprs->getProfessionnel()->getNom()),
                          array('source' => '[PRENOM_TRAITANT]',
                            'cible' => $patprs->getProfessionnel()->getPrenom()),
                          array('source' => '[TELEPHONE_TRAITANT]',
                            'cible' => $patprs->getProfessionnel()->getTelephoneFixe()),
                          array('source' => '[TELEPHONE_MOBILE_TRAITANT]',
                            'cible' => $patprs->getProfessionnel()->getTelephoneMobile()),
                        ));
                    }
                }
            }
        }

        foreach($tab_key_exp as $key_exp){
            $texte = str_replace($key_exp['source'], $key_exp['cible'], $texte);
        }

        return $texte;
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Création des action de bilan biologique et d'injection
     */
    public function createInjectionBilan($patient, $date = null, $first = false)
    {
      $entityManager = $this
      ->container
      ->get('doctrine.orm.entity_manager');

      $statut = $this->container->get('app.repository.action.statut')->find($this->container->getParameter("action_statut")["en_cours"]);
      $today = new \Datetime();
      $injection1 = $patient->getDateInjection()?date_create_from_format('d/m/Y',$patient->getDateInjection()->format('d/m/Y')):'';
      $injection2 = $patient->getSecondeInjection()?date_create_from_format('d/m/Y',$patient->getSecondeInjection()->format('d/m/Y')):'';
      $analyse1 = $patient->getDatePremiereAnalyse()?date_create_from_format('d/m/Y',$patient->getDatePremiereAnalyse()->format('d/m/Y')):'';
      $analyse2 = $patient->getDateSecondeAnalyse()?date_create_from_format('d/m/Y',$patient->getDateSecondeAnalyse()->format('d/m/Y')):'';
      if ($first) {
        //Création des premières injections et analyses si création du compte de patient naïf

        if ($patient->getInitiationTraitement()) {

          if ($today < $injection1) {

            $type = $this->container->get('app.repository.action.type')->find($this->container->getParameter("action_type")["premiere_injection"]);
            $action_injection = $this->setActionGenerique(
              $patient,
              null,
              $this->container->get('security.token_storage')->getToken()->getUser(),
              null,
              $type,
              $statut,
              new \Datetime,
              $injection1,
              null,
              null
            );

            $date_suivi = new \DateTime();
            $date_suivi->setDate($injection1->format('Y'), $injection1->format('m'), $injection1->format('d'));
            $type_suivi = $this->container->get('app.repository.action.type')->find($this->container->getParameter("action_type")["rappel_premiere_injection"]);
            $rappel_injection1 = $injection1->modify('-2 days');
            if ($today < $rappel_injection1) {
                $action_suivi = $this->setActionGenerique(
                  $patient,
                  null,
                  $this->container->get('security.token_storage')->getToken()->getUser(),
                  null,
                  $type_suivi,
                  $statut,
                  new \Datetime,
                  $rappel_injection1,
                  null,
                  $action_injection
                );
            }else{
                $action_suivi = $this->setActionGenerique(
                  $patient,
                  null,
                  $this->container->get('security.token_storage')->getToken()->getUser(),
                  null,
                  $type_suivi,
                  $statut,
                  new \Datetime,
                  $today,
                  null,
                  $action_injection
                );
            }
          }else{
              $type = $this->container->get('app.repository.action.type')->find($this->container->getParameter("action_type")["premiere_injection"]);
              $statut2 = $this->container->get('app.repository.action.statut')->find($this->container->getParameter("action_statut")["traite"]);
              $action_injection = $this->setActionGenerique(
                $patient,
                null,
                $this->container->get('security.token_storage')->getToken()->getUser(),
                null,
                $type,
                $statut2,
                new \Datetime,
                $injection1,
                $injection1,
                null
              );
          }


          if ($patient->getLieuSecondeInjection() == 1) {

            $type = $this->container->get('app.repository.action.type')->find($this->container->getParameter("action_type")["seconde_injection"]);
            $action_injection = $this->setActionGenerique(
              $patient,
              null,
              $this->container->get('security.token_storage')->getToken()->getUser(),
              null,
              $type,
              $statut,
              new \Datetime,
              $injection2,
              null,
              null
            );

            $date_suivi = new \DateTime();
            $date_suivi->setDate($injection2->format('Y'), $injection2->format('m'), $injection2->format('d'));
            $type_suivi = $this->container->get('app.repository.action.type')->find($this->container->getParameter("action_type")["rappel_seconde_injection"]);
            $action_suivi = $this->setActionGenerique(
              $patient,
              null,
              $this->container->get('security.token_storage')->getToken()->getUser(),
              null,
              $type_suivi,
              $statut,
              new \Datetime,
              $date_suivi->modify('-2 days'),
              null,
              $action_injection
            );

            $date_suivi = new \DateTime();
            $date_suivi->setDate($injection2->format('Y'), $injection2->format('m'), $injection2->format('d'));
            $type_suivi = $this->container->get('app.repository.action.type')->find($this->container->getParameter("action_type")["suivi_injection2"]);
            $action_suivi = $this->setActionGenerique(
              $patient,
              null,
              $this->container->get('security.token_storage')->getToken()->getUser(),
              null,
              $type_suivi,
              $statut,
              new \Datetime,
              $date_suivi->modify('+2 days'),
              null,
              $action_injection
            );

          } else {

            $type = $this->container->get('app.repository.action.type')->find($this->container->getParameter("action_type")["injection"]);
            $action_injection = $this->setActionGenerique(
              $patient,
              null,
              $this->container->get('security.token_storage')->getToken()->getUser(),
              null,
              $type,
              $statut,
              new \Datetime,
              $injection2,
              null,
              null
            );

            $date_suivi = new \DateTime();
            $date_suivi->setDate($injection2->format('Y'), $injection2->format('m'), $injection2->format('d'));
            $type_suivi = $this->container->get('app.repository.action.type')->find($this->container->getParameter("action_type")["suivi_injection_domicile"]);
            $action_suivi = $this->setActionGenerique(
              $patient,
              null,
              null/*$this->container->get('security.token_storage')->getToken()->getUser()*/,
              null,
              $type_suivi,
              $statut,
              new \Datetime,
              $date_suivi->modify('+2 days'),
              null,
              $action_injection
            );

            $date_appel = new \DateTime();
            $date_appel->setDate($injection2->format('Y'), $injection2->format('m'), $injection2->format('d'));
            $type = $this->container->get('app.repository.action.type')->find($this->container->getParameter("action_type")["appel_idet_presentation"]);

              $actions_existantes = $this
                ->container
                ->get('app.repository.action')
                ->findBy(
                  [
                    'actionType' => $this->container->getParameter('action_type')['appel_idet_presentation'],
                    'patient' => $patient
                  ]
                );

              if (count($actions_existantes) == 0) {
                  $this->setActionGenerique($patient, null, $this->container->get('security.token_storage')->getToken()->getUser(), null, $type, $statut, new \Datetime, null, null, null);
              }

          }

          $type = $this->container->get('app.repository.action.type')->find($this->container->getParameter("action_type")["analyse_biologique"]);
          $analyse1 = $this->setActionGenerique(
            $patient,
            null,
            $this->container->get('security.token_storage')->getToken()->getUser(),
            null,
            $type,
            $statut,
            new \Datetime,
            $patient->getDatePremiereAnalyse(),
            null,
            null
          );
          $analyse2 = $this->setActionGenerique(
            $patient,
            null,
            $this->container->get('security.token_storage')->getToken()->getUser(),
            null,
            $type,
            $statut,
            new \Datetime,
            $patient->getDateSecondeAnalyse(),
            null,
            null
          );
          $type2 = $this->container->get('app.repository.action.type')->find($this->container->getParameter("action_type")["appel_relance_laboratoire"]);
          $this->setActionGenerique(
            $patient,
            null,
            $this->container->get('security.token_storage')->getToken()->getUser(),
            null,
            $type2,
            $statut,
            new \Datetime,
            $patient->getDatePremiereAnalyse(),
            null,
            $analyse1
          );
          $this->setActionGenerique(
            $patient,
            null,
            $this->container->get('security.token_storage')->getToken()->getUser(),
            null,
            $type2,
            $statut,
            new \Datetime,
            $patient->getDateSecondeAnalyse(),
            null,
            $analyse2
          );

          $today = new \Datetime();
          $j_plus_3 = $today->modify('+3 days');
          $actions_existantes = $this
            ->container
            ->get('app.repository.action')
            ->findBy(
              [
                'actionType' => $this->container->getParameter('action_type')['appel_labo_presentation'],
                'patient' => $patient
              ]
            );

          if (count($actions_existantes) == 0 && ($patient->getInitiationTraitement() || $patient->getPortage())) {
            $type = $this->container->get('app.repository.action.type')->find($this->container->getParameter("action_type")["appel_labo_presentation"]);
            $this->setActionGenerique($patient, null, $this->container->get('security.token_storage')->getToken()->getUser(), null, $type, $statut, new \Datetime, null, null);
          }

          $actions_existantes = $this
            ->container
            ->get('app.repository.action')
            ->findBy(
              [
                'actionType' => $this->container->getParameter('action_type')['appel_pui_presentation'],
                'patient' => $patient
              ]
            );

          if (count($actions_existantes) == 0 && ($patient->getInitiationTraitement() || $patient->getPortage())) {
            $type = $this->container->get('app.repository.action.type')->find($this->container->getParameter("action_type")["appel_pui_presentation"]);
            $this->setActionGenerique($patient, null, $this->container->get('security.token_storage')->getToken()->getUser(), null, $type, $statut, new \Datetime, null, null);
          }

        }else {

            if($injection1 != ''){
                $type = $this->container->get('app.repository.action.type')->find($this->container->getParameter("action_type")["injection"]);
                $action_injection = $this->setActionGenerique(
                    $patient,
                    null,
                    $this->container->get('security.token_storage')->getToken()->getUser(),
                    null,
                    $type,
                    $statut,
                    new \Datetime,
                    $injection1,
                    null,
                    null
                );

                $date_suivi = new \DateTime();
                $date_suivi->setDate($injection1->format('Y'), $injection1->format('m'), $injection1->format('d'));
                $type_suivi = $this->container->get('app.repository.action.type')->find($this->container->getParameter("action_type")["suivi_injection_domicile"]);
                $action_suivi = $this->setActionGenerique(
                    $patient,
                    null,
                    $this->container->get('security.token_storage')->getToken()->getUser(),
                    null,
                    $type_suivi,
                    $statut,
                    new \Datetime,
                    $date_suivi->modify('+2 days'),
                    null,
                    $action_injection
                );
            }

        }


        $this->setActionRappelPlanif($patient, $patient->getDateInjection()?$patient->getDateInjection():new \Datetime, true, $action_injection);

        $entityManager->flush();

      }

      /*if($patient->getProtocole()->getId() == $this->container->getParameter("protocole")["hebdomadaire"]){
          $this->setActionHebdomadaire($patient, $date, $first);
      }elseif($patient->getProtocole()->getId() == $this->container->getParameter("protocole")["bimensuel"]){
          //Protocol
          $this->setActionBimensuel($patient, $date, $first);
      }*/
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Création d'une ligne de portage
     */
    public function createPortage($patient, $datas, $first = false)
    {

        $entityManager = $this
          ->container
          ->get('doctrine.orm.entity_manager');

        $action = $this
          ->container
          ->get('app.repository.action')
          ->find($datas['action_id']);

        //$patient = $action->getPatient();

        /*$portages_existant = $action = $this
          ->get('app.repository.patient')
          ->hasPortage($patient->getId());
        if($portages_existant && $patient){
            $action->setDosesInitiales();
            $action->setPortage(1);
            $entityManager->persist($action);
            $entityManager->flush();
        }*/

        $portage = new Portage;
        $portage->setAction($action);
        $portage->setNbBoites($datas['nbBoites']);
        $portage->setCommentaire($datas['commentaire']);

        $entityManager->persist($portage);
        $entityManager->flush();

    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Création des action de bilan biologique et d'injection
     */
    public function setActionRappelPlanif($patient, $date, $first = false)
    {

      // Création des suivis lors de la création de la seconde injection (1ère via l'application)
      $request = new Request;

      if($date) {

        $rappel_date_base = new \DateTime();
        $rappel_date_base->setDate($date->format('Y'), $date->format('m'), $date->format('d'));
        $rappel_date_base->format('Y-m-d');

        // Plage horaire par défaut
        $plage_horaire = $this->container->get('app.repository.plage.horaire')->findAll()[0];
        $plage_horaire_id = $plage_horaire->getId();
        $plage_horaire_heure = $plage_horaire->getDebut();

        // Recherche d'une préférence patient correspondant à la date effective de rappel
        $patientPreferenceHoraires = $patient->getPreferenceHoraires();

        foreach ($patientPreferenceHoraires as $patientPreferenceHoraire) {
          if (
            method_exists($patientPreferenceHoraire, 'get' . $rappel_date_base->format('d/m/y')) &&
            true === $patientPreferenceHoraire->{'get' . $rappel_date_base}()
          ) {
            $plage_horaire_id = $patientPreferenceHoraire->getPreferencePlageHoraire()->getId();
            $plage_horaire_heure = $patientPreferenceHoraire->getPreferencePlageHoraire()->getDebut();
          }
        }

        if ($first) {
          //if ($patient->getInitiationTraitement()) {

            $request->attributes->set('plage_horaire_id', $plage_horaire_id);
            $request->attributes->set('patient_id', $patient->getId());
            $request->attributes->set('rappel_heure', $plage_horaire_heure->format('H'));
            $request->attributes->set('rappel_minute', $plage_horaire_heure->format('i'));
            $request->attributes->set('rappel_fixe', false);
            $request->attributes->set('rappel_date', $rappel_date_base);

            $rappel_date_m12 = new \DateTime();
            $rappel_date_m12->setDate($rappel_date_base->format('Y'), $rappel_date_base->format('m'), $rappel_date_base->format('d'));
            $rappel_date_m12 = $this->container->get('app.date.helper')->getNextAvailableDay($rappel_date_m12->modify('+330 days'));


            // Recherche de la date de rappel en prenant en compte les week-end et les jours fériés
            $rappel_date_m12 = $this
              ->container
              ->get('app.date.helper')
              ->getNextAvailableDay($rappel_date_m12)
              ->getTimestamp();
            // Surcharge de l'objet Request
            $request->attributes->set('rappel_date', $rappel_date_m12);

            $this->container->get('app.manager.rappel')->createRappel($request, 'm12');

            $rappel_date_m6 = new \DateTime();
            $rappel_date_m6->setDate($rappel_date_base->format('Y'), $rappel_date_base->format('m'), $rappel_date_base->format('d'));
            $rappel_date_m6 = $this->container->get('app.date.helper')->getNextAvailableDay($rappel_date_m6->modify('+150 days'));

            // Recherche de la date de rappel en prenant en compte les week-end et les jours fériés
            $rappel_date_m6 = $this
              ->container
              ->get('app.date.helper')
              ->getNextAvailableDay($rappel_date_m6)
              ->getTimestamp();

            // Surcharge de l'objet Request
            $request->attributes->set('rappel_date', $rappel_date_m6);

            $this->container->get('app.manager.rappel')->createRappel($request, 'm6');

          //}
        }

      }

    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Création des action de bilan biologique et d'injection pour le protocole hebdomadaire
     */
    public function setActionHebdomadaire($patient, $date, $first = false)
    {
        $actions = $this->container->get('app.repository.action')->findBy(
          [
            'actionType' => $this->container->get('app.repository.action.type')->find($this->container->getParameter('action_type')['injection']),
            'patient' => $patient
          ]
        );
        // Si il exite au moins 2 injections et que le nombre d'injection est paire, crée une action analyse bio.

        if(count($actions) >= 2 && count($actions) %2 == 0){

            // J-2 avant l'injection // Doit recréer completement un objet date sinon bug ?
            if($date){
                $dateBilan = date_create_from_format('d/m/Y', $date->format('d/m/Y'));
                $dateBilan = $this->container->get('app.date.helper')->getNextAvailableDay($dateBilan->modify('-2 day'));
            }else{
                $dateBilan = '';
            }
            $type = $this->container->get('app.repository.action.type')->find($this->container->getParameter("action_type")["analyse_biologique"]);
            $statut = $this->container->get('app.repository.action.statut')->find($this->container->getParameter("action_statut")["en_cours"]);
            $this->setActionGenerique($patient, null, $this->container->get('security.token_storage')->getToken()->getUser(), null, $type, $statut, new \Datetime, $dateBilan, null);

        }

        // Création de l'injection
        $type = $this->container->get('app.repository.action.type')->find($this->container->getParameter("action_type")["injection"]);
        $statut = $this->container->get('app.repository.action.statut')->find($this->container->getParameter("action_statut")["en_cours"]);
        $this->setActionGenerique($patient, null, $this->container->get('security.token_storage')->getToken()->getUser(), null, $type, $statut, new \Datetime, $date, null);

        // Création des suivis lors de la création de la seconde injection (1ère via l'application)
        if($first){

            $request = new Request;

            /*
             * Création du rappel/action M12
             */
            if($date) {
                $rappel_date_base = new \DateTime($date->format('Y-m-d'));
                $rappel_date_base->modify('+1 day');
                // Recherche de la date de rappel en prenant en compte les week-end et les jours fériés
                $rappel_date = $this
                  ->container
                  ->get('app.date.helper')
                  ->getNextAvailableDay($rappel_date_base)
                  ->getTimestamp();

                // Plage horaire par défaut
                $plage_horaire = $this->container->get('app.repository.plage.horaire')->findAll()[0];
                $plage_horaire_id = $plage_horaire->getId();
                $plage_horaire_heure = $plage_horaire->getDebut();

                // Recherche d'une préférence patient correspondant à la date effective de rappel
                $patientPreferenceHoraires = $patient->getPreferenceHoraires();

                foreach ($patientPreferenceHoraires as $patientPreferenceHoraire) {
                    if (
                      method_exists($patientPreferenceHoraire, 'get' . $rappel_date) &&
                      true === $patientPreferenceHoraire->{'get' . $rappel_date}()
                    ) {
                        $plage_horaire_id = $patientPreferenceHoraire->getPreferencePlageHoraire()->getId();
                        $plage_horaire_heure = $patientPreferenceHoraire->getPreferencePlageHoraire()->getDebut();
                    }
                }

                $request->attributes->set('plage_horaire_id', $plage_horaire_id);
                $request->attributes->set('patient_id', $patient->getId());
                $request->attributes->set('rappel_heure', $plage_horaire_heure->format('H'));
                $request->attributes->set('rappel_minute', $plage_horaire_heure->format('i'));
                $request->attributes->set('rappel_fixe', false);

                // Surcharge de l'objet Request
                $request->attributes->set('rappel_date', $rappel_date);

                $this->container->get('app.manager.rappel')->createRappel($request, 'premiere_injection');

                /*
                 * Création du rappel/action M12
                 */

                $rappel_date_suivi = new \DateTime($rappel_date_base->format('Y-m-d'));
                $rappel_date_suivi->modify('+12 month');


                // Recherche de la date de rappel en prenant en compte les week-end et les jours fériés
                $rappel_date_suivi = $this
                  ->container
                  ->get('app.date.helper')
                  ->getNextAvailableDay($rappel_date_suivi)
                  ->getTimestamp();
                // Surcharge de l'objet Request
                $request->attributes->set('rappel_date', $rappel_date_suivi);

                $this->container->get('app.manager.rappel')->createRappel($request, 'm12');

                /*
                 * Création du rappel/action M6
                 */

                $rappel_date_suivi = new \DateTime($rappel_date_base->format('Y-m-d'));
                $rappel_date_suivi->modify('+6 month');

                // Recherche de la date de rappel en prenant en compte les week-end et les jours fériés
                $rappel_date_suivi = $this
                  ->container
                  ->get('app.date.helper')
                  ->getNextAvailableDay($rappel_date_suivi)
                  ->getTimestamp();

                // Surcharge de l'objet Request
                $request->attributes->set('rappel_date', $rappel_date_suivi);

                $this->container->get('app.manager.rappel')->createRappel($request, 'm6');

                /*
                 * Création du rappel/action M3
                 */

                $rappel_date_suivi = new \DateTime($rappel_date_base->format('Y-m-d'));
                $rappel_date_suivi->modify('+3 month');

                // Recherche de la date de rappel en prenant en compte les week-end et les jours fériés
                $rappel_date_suivi = $this
                  ->container
                  ->get('app.date.helper')
                  ->getNextAvailableDay($rappel_date_suivi)
                  ->getTimestamp();
                // Surcharge de l'objet Request
                $request->attributes->set('rappel_date', $rappel_date_suivi);

                $this->container->get('app.manager.rappel')->createRappel($request, 'm3');

                /*
                 * Création du rappel/action M2
                 */

                $rappel_date_suivi = new \DateTime($rappel_date_base->format('Y-m-d'));
                $rappel_date_suivi->modify('+2 month');

                // Recherche de la date de rappel en prenant en compte les week-end et les jours fériés
                $rappel_date_suivi = $this
                  ->container
                  ->get('app.date.helper')
                  ->getNextAvailableDay($rappel_date_suivi)
                  ->getTimestamp();
                // Surcharge de l'objet Request
                $request->attributes->set('rappel_date', $rappel_date_suivi);

                $this->container->get('app.manager.rappel')->createRappel($request, 'm2');

                /*
                 * Création du rappel/action M1
                 */

                $rappel_date_suivi = new \DateTime($rappel_date_base->format('Y-m-d'));
                $rappel_date_suivi->modify('+1 month');

                // Recherche de la date de rappel en prenant en compte les week-end et les jours fériés
                $rappel_date_suivi = $this
                  ->container
                  ->get('app.date.helper')
                  ->getNextAvailableDay($rappel_date_suivi)
                  ->getTimestamp();
                // Surcharge de l'objet Request
                $request->attributes->set('rappel_date', $rappel_date_suivi);

                $this->container->get('app.manager.rappel')->createRappel($request, 'm1');
            }
        }
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Création des action de bilan biologique et d'injection pour le protocole bimensuel
     */
    public function setActionBimensuel($patient, $date, $first = false)
    {

        $now = new DateTime();

        // Création de la première analyse bio. J-9 // Doit recréer completement un objet date sinon bug ?
        $dateBilan = date_create_from_format('d/m/Y', $date->format('d/m/Y'));
        $dateBilan->modify('-9 day');

        // Vérifie que $dateBilan soit dans le futur
        if($dateBilan > $now){
            $type = $this->container->get('app.repository.action.type')->find($this->container->getParameter("action_type")["analyse_biologique"]);
            $statut = $this->container->get('app.repository.action.statut')->find($this->container->getParameter("action_statut")["en_cours"]);
            $this->setActionGenerique($patient, null, $this->container->get('security.token_storage')->getToken()->getUser(), null, $type, $statut, new \Datetime, $dateBilan, null);
        }


        // Création de la 2ème analyse bio. J-2 // Doit recréer completement un objet date sinon bug ?
        $dateBilan = date_create_from_format('d/m/Y', $date->format('d/m/Y'));
        $dateBilan->modify('-2 day');

        // Vérifie que $dateBilan soit dans le futur
        if($dateBilan > $now){
            $type = $this->container->get('app.repository.action.type')->find($this->container->getParameter("action_type")["analyse_biologique"]);
            $statut = $this->container->get('app.repository.action.statut')->find($this->container->getParameter("action_statut")["en_cours"]);
            $this->setActionGenerique($patient, null, $this->container->get('security.token_storage')->getToken()->getUser(), null, $type, $statut, new \Datetime, $dateBilan, null);
        }

        // Création de l'injection
        $type = $this->container->get('app.repository.action.type')->find($this->container->getParameter("action_type")["injection"]);
        $statut = $this->container->get('app.repository.action.statut')->find($this->container->getParameter("action_statut")["en_cours"]);
        $this->setActionGenerique($patient, null, $this->container->get('security.token_storage')->getToken()->getUser(), null, $type, $statut, new \Datetime, $date, null);
    }

    /**
     * Génération du PDF de contrat IDET (envoyé à l'IDET par email)
     * @param Patient $patient
     * @return Array
     */
    public function createContratIdePdf($ide_t)
    {

        $entityManager = $this
          ->container
          ->get('doctrine.orm.entity_manager');

        $document	= new Document();

        $directory 	= $this->container->getParameter('directory')['contrat_idet']. $ide_t->getId().'/';
        $filename 	= $this->container->get('app.generic.helper')->getUniqueFileName('Axess_contrat_idet_'.$ide_t->getId().'_'.date('YmdHis').'.pdf', $directory);
        $pdf = $this->container->get('templating')->render('@AppBundle/Patient/contrat.idet.html.twig',['idet'=>$ide_t]);

        $fullname	=	 $directory . '' . $filename;
        $this->container->get('knp_snappy.pdf')->generateFromHtml($pdf, $fullname);
        $savename = 'contrat_idet/'.$ide_t->getId().'/'.$filename;
        $document->setDocument($savename);
        $document->setNom($filename);

        /*$directory = $this->getParameter('directory')['documents'];
        $filename  = $document->getDocument();*/

        $filePath = $fullname;

        // Check if file exists
        $fs = new Filesystem;

        if (false === $fs->exists($filePath))
        {
            throw $this->createNotFoundException('test');
        }
        /*
        // Prepare BinaryFileResponse
        $response = new BinaryFileResponse($filePath);
        $response->trustXSendfileTypeHeader();

        return $response->setContentDisposition(
          ResponseHeaderBag::DISPOSITION_ATTACHMENT,
          basename($filename),
          iconv('UTF-8', 'ASCII//TRANSLIT', basename($filename))
        );*/

        $entityManager->persist($document);
        $entityManager->flush();

        return $document;
    }

    public function createMandatPdf($professionnel,$patient){

        $document	= new Document();
        $ide_t = $professionnel;
        $directory 	= $this->container->getParameter('directory')['mandat_patient']. $patient->getId().'/';;
        $filename 	= $this->getUniqueFileName('Axess_mandat_'.$patient->getId().'_'.date('YmdHis').'.pdf', $directory);
        $pdf = $this->container->get('templating')->render('@AppBundle/Patient/mandat.html.twig',['patient'=>$patient,'idet'=>$ide_t]);

        $fullname	=	 $directory . '/' . $filename;
        $this->container->get('knp_snappy.pdf')->generateFromHtml($pdf, $fullname);
        $document->setDocument('mandat_patient/'.$patient->getId().'/'.$filename);
        $document->setNom($filename);

        //$directory = $this->getParameter('directory')['documents'];
        //$filename  = $document->getDocument();

        $filePath = $fullname;

        // Check if file exists
        $fs = new Filesystem;

        if (false === $fs->exists($filePath))
        {
            throw $this->createNotFoundException('test');
        }
        /*
        // Prepare BinaryFileResponse
        $response = new BinaryFileResponse($filePath);
        $response->trustXSendfileTypeHeader();

        return $response->setContentDisposition(
          ResponseHeaderBag::DISPOSITION_ATTACHMENT,
          basename($filename),
          iconv('UTF-8', 'ASCII//TRANSLIT', basename($filename))
        );*/
        return $document;
    }
}
