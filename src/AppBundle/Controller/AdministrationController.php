<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\HttpFoundation\JsonResponse;

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;

use AppBundle\Entity\User;
use AppBundle\Entity\AccessConcurrent;

use AppBundle\Form\AdministrationUserType;
use AppBundle\Form\ProfessionnelType;
use AppBundle\Form\SearchProfessionnelType;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


class AdministrationController extends Controller
{
    /**
     * Gestion des accès concurents à la fiche patient
     */
    public function preExecute()
    {
        $this->get('app.manager.concurrent.acces')->removePatientAccess();
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Liste paginée des opérations
     * @param $page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function listeAction(Request $request, $page)
    {
        $search = null;
        $session = $this->get('session');
        $page = (1 > $page) ? 1 : $page;

        // Taille de la pagination depuis les configs
        $nbPerPage = 20;

        // Liste des utilisateurs
        $repository = $this->getDoctrine()->getRepository('AppBundle:Professionnel');


        if ($request->get('reset')) {
            $session->set('search_professionnel', null);
            return $this->redirectToRoute('app_administration_liste');
        }
        // Filtre les cibles suivant les criteres de recherche sinon récupére toutes les cible
        if ($request->get('search_professionnel') != null) {
            $search = $request->get('search_professionnel');
            $session->set('search_professionnel', $search);
            $professionnels = $repository->getPaginatedProfessionnels($page, $nbPerPage, $search);
        } elseif ($session->get('search_professionnel') != null) {

            $search = $session->get('search_professionnel');
            $professionnels = $repository->getPaginatedProfessionnels($page, $nbPerPage, $search);
        } else {
            $professionnels = $repository->getPaginatedProfessionnels($page, $nbPerPage, null);
        }
        
        // Calcul du nombre total de pages
        $nbPages = ceil(count($professionnels) / $nbPerPage);

        // Si la page n'existe pas, on retourne sur la première page
        if (0 !== (int)$nbPages && $page > $nbPages)
        {
            return $this->redirect($this->generateUrl('app_administration_liste', ['page' => 1]));
        }
 
        if($search != null){
            if($search['dateDebut'] == ''){
                unset($search['dateDebut']);
            }else{
                $search['dateDebut'] = date_create_from_format('Y-m-d H:i:s', $search['dateDebut']. ' 00:00:00');
            }
            if($search['dateFin'] == ''){
                unset($search['dateFin']);
            }else{
                $search['dateFin'] = date_create_from_format('Y-m-d H:i:s', $search['dateFin']. ' 00:00:00');
            }

            if($search['formationInitiale'] == ''){
                unset($search['formationInitiale']);
            }else{
                $search['formationInitiale'] = date_create_from_format('Y-m-d H:i:s', $search['formationInitiale']. ' 00:00:00');
            }

            $hour = sprintf('%02d', $search['heure']['hour']).':'.sprintf('%02d', $search['heure']['minute']);

            if($hour == '00:00'){
                unset($search['heure']);
            }else{
                $search['heure'] = date_create_from_format('H:i', $hour);
            }


            if($search['profil'] != ''){
                $search['profil'] = $this->getDoctrine()->getRepository('AppBundle:Profil')->find($search['profil']);
            }

            if($search['statut'] != ''){
                $search['statut'] = $this->getDoctrine()->getRepository('AppBundle:Statut')->find($search['profil']);
            }

            if($search['statutProfessionnel'] != ''){
                $search['statutProfessionnel'] = $this->getDoctrine()->getRepository('AppBundle:StatutProfessionnel')->find($search['profil']);
            }

            if($search['titreComplement'] != ''){
                $search['titreComplement'] = $this->getDoctrine()->getRepository('AppBundle:TitreComplement')->find($search['titreComplement']);
            }

            if($search['titreComplementAutres'] != ''){
                $search['titreComplementAutres'] = $this->getDoctrine()->getRepository('AppBundle:TitreComplement')->find($search['titreComplementAutres']);
            }

        }

        $form = $this->get('form.factory')->create(SearchProfessionnelType::class, $search);

        return $this->render('AppBundle:Administration:liste.html.twig', [
            'form' => $form->createView(),
            'professionnels'   => $professionnels,
            'nbPages' => $nbPages,
            'page'    => $page
        ]);
    }
    
    // -----------------------------------------------------------------------------------------------------------------


    public function ficheAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $block = false;
        // On récupère l'annonce $id
        $professionnel = $em->getRepository('AppBundle:Professionnel')->find($request->get('professionnel_id'));
        if (null === $professionnel) {
            throw new NotFoundHttpException("Le professionnel n'existe pas.");
        }

        if(count($professionnel->getAccess()) > 0){
            foreach($professionnel->getAccess() as $acces){
                if($acces->getUtilisateur() == $this->getUser()){
                    $acces->setDate(new \DateTime);
                    $em->flush();
                }else{
                    $block = true;
                }
            }
        }else{
            //Ajout access concurrent
            $access = new AccessConcurrent();
            $access->setProfessionnel($professionnel);
            $access->setUtilisateur($this->getUser());
            $access->setDate(new \DateTime);
            $em->persist($access);
            $em->flush();
        }

        $form = $this->get('form.factory')->create(ProfessionnelType::class, $professionnel);

        if ($form->handleRequest($request)->isValid()){

            $em = $this->getDoctrine()->getManager();
            $em->persist($professionnel);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'La professionnel a été mis à jour');

            return $this->redirectToRoute('app_administration_liste');
        }

        return $this->render('AppBundle:Administration:fiche.html.twig', array(
            'form'   => $form->createView(),
            'professionnel' => $professionnel,
            'block' => $block
        ));
    }
    // -----------------------------------------------------------------------------------------------------------------

    public function excelAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $search = $this->get('session')->get('search_professionnel');
        $professionnels = $em->getRepository('AppBundle:Professionnel')->getListeActuelle($search);

        // ask the service for a Excel5
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties()->setCreator("liuggio")
            ->setLastModifiedBy("Giulio De Donato")
            ->setTitle("Office 2005 XLSX Test Document")
            ->setSubject("Office 2005 XLSX Test Document")
            ->setDescription("Test document for Office 2005 XLSX, generated using PHP classes.")
            ->setKeywords("office 2005 openxml php")
            ->setCategory("Test result file");

        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'PROFIL')
            ->setCellValue('B1', 'ROLE')
            ->setCellValue('C1', 'TITRE')
            ->setCellValue('D1', 'NOM')
            ->setCellValue('E1', 'PRENOM')
            ->setCellValue('F1', 'MOBILE')
            ->setCellValue('G1', 'EMAIL')
            ->setCellValue('H1', 'TITRE COMPLEMENTAIRE')
            ->setCellValue('I1', 'CODE POSTAL')
            ->setCellValue('J1', 'DATE DE NAISSANCE')
            ->setCellValue('K1', 'LIBERAL/SALARIE')
            ->setCellValue('L1', 'STATUT')
            ->setCellValue('M1', 'CONNEXION HAUT DEBIT')
            ->setCellValue('N1', 'COMPETENCE')
            ->setCellValue('O1', 'SPECIALITE')
            ->setCellValue('P1', 'MEMBRE APHP')
            ->setCellValue('Q1', 'N° RPPS')
            ->setCellValue('R1', 'DISPONIBILITE MEDECIN')
            ->setCellValue('S1', 'PICPUS')
            ->setCellValue('T1', 'DISTANCE')
            ->setCellValue('U1', 'DATE CREATION')
            ->setCellValue('V1', 'DATE FORMATION INITIALE')
            ->setCellValue('W1', 'HEURE FORMATION INITIALE')
            ->setCellValue('X1', 'HABILITATION')
            ->setCellValue('Y1', 'BADGE')
            ->setCellValue('Z1', 'CONVOCATION')
            ->setCellValue('AA1', 'PARTICIPATION')
            ->setCellValue('AB1', 'FORMATION EFFECTUE')
            ->setCellValue('AC1', 'INDISPO LUNDI MATIN')
            ->setCellValue('AD1', 'INDISPO LUNDI APREM')
            ->setCellValue('AE1', 'INDISPO MARDI MATIN')
            ->setCellValue('AF1', 'INDISPO MARDI APREM')
            ->setCellValue('AG1', 'INDISPO MERCREDI MATIN')
            ->setCellValue('AH1', 'INDISPO MERCREDI APREM')
            ->setCellValue('AI1', 'INDISPO JEUDI MATIN')
            ->setCellValue('AJ1', 'INDISPO JEUDI APREM')
            ->setCellValue('AK1', 'INDISPO VENDREDI MATIN')
            ->setCellValue('AL1', 'INDISPO VENDREDI APREM')
            ->setCellValue('AM1', 'INDISPO SAMEDI MATIN')
            ->setCellValue('AN1', 'INDISPO SAMEDI APREM')
            ->setCellValue('AO1', 'INDISPO DIMANCHE MATIN')
            ->setCellValue('AP1', 'INDISPO DIMANCHE APREM')
        ;

        $row = 2;
        
        foreach($professionnels as $professionnel){
            $phpExcelObject->getActiveSheet()
                ->setCellValue('A'.$row, $professionnel->getProfil() == null ? '' : $professionnel->getProfil()->getLabel())
                ->setCellValue('B'.$row, $professionnel->getRole() == null ? '' : $professionnel->getRole()->getLabel())
                ->setCellValue('C'.$row, $professionnel->getTitre() == null ? '' : $professionnel->getTitre()->getLabel())
                ->setCellValue('D'.$row, $professionnel->getNom())
                ->setCellValue('E'.$row, $professionnel->getPrenom())
                ->setCellValue('F'.$row, $professionnel->getMobile())
                ->setCellValue('G'.$row, $professionnel->getEmail())
                ->setCellValue('H'.$row, $professionnel->getTitreComplement() == null ? '' : $professionnel->getTitreComplement()->getLabel())
                ->setCellValue('I'.$row, $professionnel->getCodePostal())
                ->setCellValue('J'.$row, $professionnel->getDateNaissance()->format('d/m/Y'))
                ->setCellValue('K'.$row, $professionnel->getStatut() == null ? '' : $professionnel->getStatut()->getLabel())
                ->setCellValue('L'.$row, $professionnel->getStatutProfessionnel() == null ? '' : $professionnel->getStatutProfessionnel()->getLabel())
                ->setCellValue('M'.$row, $professionnel->getPrerequis() == 1 ? 'Oui' : 'Non')
                ->setCellValue('N'.$row, $professionnel->getCompetence() == null ? '' : $professionnel->getCompetence()->getLabel())
                ->setCellValue('O'.$row, $professionnel->getSpecialite() == null ? '' : $professionnel->getSpecialite()->getLabel())
                ->setCellValue('P'.$row, $professionnel->getMembre() == 1 ? 'Oui' : 'Non')
                ->setCellValue('Q'.$row, $professionnel->getRpps())
                ->setCellValue('R'.$row, $professionnel->getDisponibilite() == 1 ? 'Oui' : 'Non')
                ->setCellValue('S'.$row, $professionnel->getPicpus() == 1 ? 'Oui' : 'Non')
                ->setCellValue('T'.$row, $professionnel->getDistance() == 1 ? 'Oui' : 'Non')
                ->setCellValue('U'.$row, $professionnel->getDateCreation()->format('d/m/Y H:i:s'))
                ->setCellValue('V'.$row, $professionnel->getFormationInitiale() == null ? '' : $professionnel->getFormationInitiale()->format('d/m/Y'))
                ->setCellValue('W'.$row, $professionnel->getHeure() == null ? '' : $professionnel->getHeure()->format('H:i'))
                ->setCellValue('X'.$row, $professionnel->getHabilitation() == null ? '' : $professionnel->getHabilitation()->getLabel())
                ->setCellValue('Y'.$row, $professionnel->getBadge() == null ? '' : $professionnel->getBadge()->getLabel())
                ->setCellValue('Z'.$row, $professionnel->getConvocation() == 1 ? 'Oui' : 'Non')
                ->setCellValue('AA'.$row, $professionnel->getParticipation() == 1 ? 'Oui' : 'Non')
                ->setCellValue('AB'.$row, $professionnel->getFormationEffectue() == 1 ? 'Oui' : 'Non')
                ->setCellValue('AC'.$row, $professionnel->getLundiMatin() == 1 ? 'Oui' : 'Non')
                ->setCellValue('AD'.$row, $professionnel->getLundiAprem() == 1 ? 'Oui' : 'Non')
                ->setCellValue('AE'.$row, $professionnel->getMardiMatin() == 1 ? 'Oui' : 'Non')
                ->setCellValue('AF'.$row, $professionnel->getMardiAprem() == 1 ? 'Oui' : 'Non')
                ->setCellValue('AG'.$row, $professionnel->getMercrediMatin() == 1 ? 'Oui' : 'Non')
                ->setCellValue('AH'.$row, $professionnel->getMercrediAprem() == 1 ? 'Oui' : 'Non')
                ->setCellValue('AI'.$row, $professionnel->getJeudiMatin() == 1 ? 'Oui' : 'Non')
                ->setCellValue('AJ'.$row, $professionnel->getJeudiAprem() == 1 ? 'Oui' : 'Non')
                ->setCellValue('AK'.$row, $professionnel->getVendrediMatin() == 1 ? 'Oui' : 'Non')
                ->setCellValue('AL'.$row, $professionnel->getVendrediAprem() == 1 ? 'Oui' : 'Non')
                ->setCellValue('AM'.$row, $professionnel->getSamediMatin() == 1 ? 'Oui' : 'Non')
                ->setCellValue('AN'.$row, $professionnel->getSamediAprem() == 1 ? 'Oui' : 'Non')
                ->setCellValue('AO'.$row, $professionnel->getDimancheMatin() == 1 ? 'Oui' : 'Non')
                ->setCellValue('AP'.$row, $professionnel->getDimancheAprem() == 1 ? 'Oui' : 'Non');
            $row++;
        }

        // Auto Size
        $sheet = $phpExcelObject->getActiveSheet();
        $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells( true );
        /** @var PHPExcel_Cell $cell */
        foreach( $cellIterator as $cell ) {
            $sheet->getColumnDimension( $cell->getColumn() )->setAutoSize( true );
        }

        //Bold
        $phpExcelObject->getActiveSheet()->getStyle('A1:AQ1')->getFont()->setBold(true);

        $phpExcelObject->getActiveSheet()->setTitle('Résultat');
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'Export_Covidom.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function accessAction(Request $request)
    {

        if ($request->isXmlHttpRequest()) {
            $data = null;

            $professionnel = $this
                ->getDoctrine()->getRepository('AppBundle:Professionnel')
                ->find($request->get('professionnel_id'));

            if ($professionnel) {
                $em = $this->getDoctrine()->getManager();
                $access = $this
                    ->getDoctrine()->getRepository('AppBundle:AccessConcurrent')
                    ->findBy([
                        'professionnel' => $professionnel->getId(),
                        'utilisateur' => $this->getUser()->getId()
                    ]);

                foreach($access as $acces){
                    $acces->setDate(new \DateTime);
                    $em->flush();
                }
            }
        }

        return new JsonResponse($data);
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function deleteAccessAction(Request $request)
    {
        $data = null;

        if ($request->isXmlHttpRequest()) {
            $data = null;

            $professionnel = $this
                ->getDoctrine()->getRepository('AppBundle:Professionnel')
                ->find($request->get('professionnel_id'));

            if ($professionnel) {
                $em = $this->getDoctrine()->getManager();
                $access = $this
                    ->getDoctrine()->getRepository('AppBundle:AccessConcurrent')
                    ->findBy([
                        'professionnel' => $professionnel->getId(),
                        'utilisateur' => $this->getUser()->getId()
                    ]);

                foreach($access as $acces){
                    $em->remove($acces);
                    $em->flush();
                }
            }
        }

        return new JsonResponse($data);
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Liste paginée des utilisateurs
     * @param $page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function utilisateursAction($page)
    {
        $page = (1 > $page) ? 1 : $page;

        // Taille de la pagination depuis les configs
        $nbPerPage = 20;

        // Liste des utilisateurs
        $repository = $this->getDoctrine()->getRepository('AppBundle:User');
        $users = $repository->getPaginatedUsers($page, $nbPerPage);

        // Calcul du nombre total de pages
        $nbPages = ceil(count($users) / $nbPerPage);

        // Si la page n'existe pas, on retourne sur la première page
        if (0 !== (int)$nbPages && $page > $nbPages)
        {
            return $this->redirect($this->generateUrl('app_administration_utilisateurs', ['page' => 1]));
        }

        return $this->render('AppBundle:Administration:utilisateurs.html.twig', [
            'users'   => $users,
            'nbPages' => $nbPages,
            'page'    => $page
        ]);
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Création d'un utilisateur
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function utilisateurAddAction(Request $request)
    {
        //Requete pour éviter l'erreur "Request Heterogeneous"
        $this->getDoctrine()->getManager()->getConnection()->exec("set quoted_identifier on;
        set ansi_warnings on;
        set ansi_nulls on;
        set CONCAT_NULL_YIELDS_NULL on;
        set ANSI_PADDING on");

        $user = new User;

        $form = $this->createForm(AdministrationUserType::class, $user);
        $form->handleRequest($request);

        // Soumission du formulaire
        if ($form->isSubmitted() && $form->isValid())
        {

            $user->setConfirmationToken($this->get('fos_user.util.token_generator')->generateToken());

            /*$confirmationUrl = $this->getParameter('hosts')['default'];
            $message = \Swift_Message::newInstance()
                ->setSubject($this->getParameter('activation')['subject'])
                ->setFrom($this->getParameter('mailer_from'), $this->getParameter('mailer_from_name'))
                ->setTo($user->getEmail())
                ->setContentType('text/html')
                ->setBody(
                    $this->render(
                        '@AppBundle/Activation/email.html.twig', [
                            'user'            => $user,
                            'confirmationUrl' => 'http://' . $confirmationUrl . '/enregistrement/confirmation/' . $user->getConfirmationToken()
                        ]
                    )
                )
            ;

            $this->get('mailer')->send($message);*/

            $entityManager = $this->get('doctrine.orm.entity_manager');

            $encoder      = $this->get('security.encoder_factory')->getEncoder($user);
            $encoded_pass = $encoder->encodePassword($user->getPassword(), $user->getSalt());

            $user->setPassword($encoded_pass);
            $user->setEnabled(True);

            // Récupération des valeurs du formulaire
            $postedValues = $request->request->get('administration_user');


            // Création du rôle
            /*foreach ($this->getParameter('profils') as $role => $profil_id)
            {
                if ((int)$profil_id === (int)$postedValues['profils'])
                {
                    $user->addRole($this->getParameter('roles')[$role]);
                }
            }*/

            $entityManager->persist($user);
            $entityManager->flush();

            $message = $this
                ->get('translator')
                ->trans("L'utilisateur a bien été enregistré.")
            ;

            $this->get('session')->getFlashBag()->set('info', $message);

            return $this->redirect($this->generateUrl('app_administration_utilisateurs'));
        }

        return $this->render('AppBundle:Administration:utilisateur.update.html.twig', [
            'form'               => $form->createView(),
            'title'              => 'Création de l\'utilisateur',
            'button'             => 'Enregistrer'
        ]);
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Édition d'un utilisateur par son identifiant
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function utilisateurUpdateAction(Request $request)
    {
        //Requete pour éviter l'erreur "Request Heterogeneous"
        $this->getDoctrine()->getManager()->getConnection()->exec("set quoted_identifier on;
        set ansi_warnings on;
        set ansi_nulls on;
        set CONCAT_NULL_YIELDS_NULL on;
        set ANSI_PADDING on");

        
        // Recherche de l'utilisateur
        $repository = $this->getDoctrine()->getRepository('AppBundle:User');
        $user = $repository->getUserById($request->get('user_id'));

        // Si l'utilisateur n'existe pas
        if (null === $user)
        {
            $message = $this
                ->get('translator')
                ->trans("L'utilisateur user_id n'existe pas.", ['%user_id%' => $request->get('user_id')])
            ;

            $this->get('session')->getFlashBag()->add('inline_error', $message);

            return $this->redirect($this->generateUrl('app_administration_utilisateurs'));
        }

        // Enregistrement temporaire du mot de passe
        $tmp_password = $user->getPassword();

        $form = $this->createForm(AdministrationUserType::class, $user);
        $form->handleRequest($request);

        // Soumission du formulaire
        if ($form->isSubmitted() && $form->isValid())
        {
            $entityManager = $this->get('doctrine.orm.entity_manager');

            // Récupération des valeurs du formulaire
            $postedValues = $request->request->get('administration_user');

            // Update de mot de passe
            if (false === empty($postedValues['password']))
            {
                $encoder      = $this->get('security.encoder_factory')->getEncoder($user);
                $encoded_pass = $encoder->encodePassword($postedValues['password'], $user->getSalt());

                $user->setPassword($encoded_pass);
            }
            // Si les champs de mot de passe sont vides on conserve l'ancien
            else
            {
                $user->setPassword($tmp_password);
            }


            // Suppression du rôle
            /*foreach ($user->getRoles() as $role)
            {
                $user->removeRole($role);
            }

            // Création du rôle
            foreach ($this->getParameter('profils') as $role => $profil_id)
            {
                if ((int)$profil_id === (int)$postedValues['profils'])
                {
                    $user->addRole($this->getParameter('roles')[$role]);
                }
            }*/

            $entityManager->persist($user);
            $entityManager->flush();

            $message = $this
                ->get('translator')
                ->trans("L'utilisateur a bien été mis à jour.")
            ;

            $this->get('session')->getFlashBag()->add('inline_success', $message);

            return $this->redirect($this->generateUrl('app_administration_utilisateurs'));
        }

        return $this->render('AppBundle:Administration:utilisateur.update.html.twig', [
            'form'               => $form->createView(),
            'title'              => 'Édition de l\'utilisateur',
            'button'             => 'Modifier'
        ]);
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Suite à la confirmation reçue par email, l'utilisateur est invité à "Créer" son mot de passe
     * @param Request $request
     * @see https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/configuration_reference.rst
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function confirmationAction(Request $request)
    {
        $current_user = $this->getUser();

        // Recherche de l'utilisateur
        $user = $this
            ->get('app.repository.user')
            ->find($current_user->getId())
        ;

        // Si l'utilisateur n'existe pas
        if (null === $user)
        {
            $message = $this
                ->get('translator')
                ->trans("Une erreur technique est survenue, veuillez contacter l'administrateur du site.")
            ;

            $this->get('session')->getFlashBag()->set('inline_error', $message);

            throw $this->createNotFoundException($message);
        }

        $form = $this->createForm(UserPasswordType::class, $user);
        $form->handleRequest($request);

        // Soumission du formulaire
        if ($form->isSubmitted() && $form->isValid())
        {
            $entityManager = $this->get('doctrine.orm.entity_manager');

            // Récupération des valeurs du formulaire
            $postedValues = $request->request->get('user_password');

            // Encodage de mot de passe
            $encoder      = $this->get('security.encoder_factory')->getEncoder($user);
            $encoded_pass = $encoder->encodePassword($postedValues['password'], $user->getSalt());

            $user->setPassword($encoded_pass);
            $entityManager->flush();

            $message = $this
                ->get('translator')
                ->trans('Votre mot de passe a bien été créé.')
            ;

            $this->get('session')->getFlashBag()->set('inline_success', $message);

            return $this->redirect($this->generateUrl('app_accueil'));
        }

        return $this->render('AppBundle:Activation:confirmation.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Redirection suite à un changement de mot de passe
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function changePasswordConfirmAction()
    {
        $current_user = $this->getUser();

        // Recherche de l'utilisateur
        $user = $this
            ->get('app.repository.user')
            ->find($current_user->getId())
        ;

        if ($user)
        {
            $message = $this
                ->get('translator')
                ->trans('Votre mot de passe a bien été modifié.')
            ;

            $this->get('session')->getFlashBag()->set('inline_success', $message);

            return $this->redirect($this->generateUrl('app_accueil'));
        }
        else
        {
            $message = $this
                ->get('translator')
                ->trans("Une erreur technique est survenue, veuillez contacter l'administrateur du site.")
            ;

            $this->get('session')->getFlashBag()->set('inline_error', $message);

            throw $this->createNotFoundException($message);
        }
    }
}
