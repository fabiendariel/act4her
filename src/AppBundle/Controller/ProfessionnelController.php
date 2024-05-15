<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\HttpFoundation\JsonResponse;

use AppBundle\Form\QuestionnairePatientType;
use AppBundle\Form\QuestionnaireType;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use AppBundle\Entity\Professionnel;
use AppBundle\Entity\Code;

class ProfessionnelController extends Controller
{
  /**
   * @Route("/professionnel", name="professionnel_index")
   */
  public function indexAction(Request $request)
  {

    $em = $this->getDoctrine()->getManager();

    $em->getConnection()->prepare('SET ANSI_NULLS ON')->execute();
    $em->getConnection()->prepare('SET ANSI_WARNINGS ON')->execute();
    $em->getConnection()->prepare('SET CONCAT_NULL_YIELDS_NULL ON')->execute();
    $em->getConnection()->prepare('SET ANSI_PADDING ON')->execute();

    $statement = $em->getConnection()->prepare('EXEC WEBInfosListing @user_id=:user_id');
    $statement->bindValue('user_id', $this->getUser()->getId());
    $statement->execute();

    $liste_suivi = $statement->fetchAll();

    $liste_patients = array();
    $a=0;
    foreach($liste_suivi as $suivi){
      $liste_patients[] = $suivi;
    }

    return $this->render('AppBundle:Professionnel:index.html.twig', ['liste_patients'=>$liste_patients]);
  }

  // -----------------------------------------------------------------------------------------------------------------

  public function patientAction(Request $request)
  {
    $em = $this->getDoctrine()->getManager();

    $em->getConnection()->prepare('SET ANSI_NULLS ON')->execute();
    $em->getConnection()->prepare('SET ANSI_WARNINGS ON')->execute();
    $em->getConnection()->prepare('SET CONCAT_NULL_YIELDS_NULL ON')->execute();
    $em->getConnection()->prepare('SET ANSI_PADDING ON')->execute();
    $em->getConnection()->prepare('SET QUOTED_IDENTIFIER ON')->execute();

    if($request->files->get('new_doc_doc')){
      if($request->files->get('new_doc_doc')->getPathName()!=""){
        $data = fopen($request->files->get('new_doc_doc')->getPathName(), 'rb');
        $size = filesize($request->files->get('new_doc_doc')->getPathName());
        $contents = fread($data, $size);
        fclose($data);
        $comm = '';
        if($request->get('new_doc_comm') && $request->get('new_doc_comm') != "")
          $comm = $request->get('new_doc_comm');
        $filename = $request->files->get('new_doc_doc')->getClientOriginalName();

        $unpacked = unpack('H*hex', $contents);
        $encoded =  '0x' . $unpacked['hex'];

        $statement = $em->getConnection()->prepare("insert into T_Document_Log (num_fiche_pat,num_fiche_prof,[id_document],[nom_fichier],[fichier],[commentaire],[date_creation])
              VALUES(:num_fiche,:num_fiche_prof,3,:nom_fichier,".$encoded.",:comm,getdate())");
        $statement->bindValue('num_fiche', $request->get('patient_id'));

        $statement->bindValue('num_fiche', $request->get('patient_id'));
        $statement->bindValue('num_fiche_prof', $this->getUser()->getProfessionnel()->getId());
        $statement->bindValue('nom_fichier', $filename);
        $statement->bindValue('comm', $comm);
        $statement->execute();

        $statement = $em->getConnection()->prepare("SELECT nom, prenom FROM T_Cible WHERE num_fiche = :num_fiche");
        $statement->bindValue('num_fiche', $request->get('patient_id'));
        $statement->execute();
        $result = $statement->fetchAll();
        $result[0]['id'] = $request->get('patient_id');
        $patient = $result[0];

        $message = \Swift_Message::newInstance()
            ->setSubject('Programme ACT4HER – Dépôt document prescripteur')
            ->setFrom($this->getParameter('mailer_from'), $this->getParameter('mailer_from_name'))
            ->setTo('genoca@test.com')
            ->setContentType('text/html')
            ->setBody($this->renderView('AppBundle:Professionnel:send_doc_add.html.twig', ['patient' => $patient, 'commentaire'=>$comm]))
        ;
        //$this->get('mailer')->send($message);
        return $this->redirect($this->generateUrl('app_patient_fiche',['patient_id'=>$request->get('patient_id')]));
      }

    }

    // Insert dernière visite
    $now = new \DateTime;
    $statement = $em->getConnection()->prepare("insert into t_log_extranet (num_fiche,date_visite) VALUES(:num_fiche,:date)");
    $statement->bindValue('num_fiche', $request->get('patient_id'));
    $statement->bindValue('date', $now->format('Y-m-d H:i:s'));
    $statement->execute();


    $statement = $em->getConnection()->prepare('EXEC WEBSuivisVip @patient_id=:patient_id');
    $statement->bindValue('patient_id', $request->get('patient_id'));
    $statement->execute();
    $suivi_vip = $statement->fetchAll();

    $statement = $em->getConnection()->prepare('EXEC WEBSuivisNonVip @patient_id=:patient_id');
    $statement->bindValue('patient_id', $request->get('patient_id'));
    $statement->execute();
    $suivi_non_vip = $statement->fetchAll();

    $suivis = [];

    foreach($suivi_non_vip as $suivi){
      $suivis[$suivi['suivi_nom']]['id_doc'] = $suivi['id_doc_log'];
      $suivis[$suivi['suivi_nom']]['obs_alerte'] = $suivi['obs_alerte'];
      $suivis[$suivi['suivi_nom']]['tolerance_alerte'] = $suivi['tolerance_alerte'];
    }

    /*
     * VERSION POUR LES PROGRAMME D'APPRENTISSAGE
    */
    $statement = $em->getConnection()->prepare('EXEC WEBPatientGet @patient_id=:patient_id');
    $statement->bindValue('patient_id', $request->get('patient_id'));
    $statement->execute();
    $infos_patient = $statement->fetchAll();

    $patient = $infos_patient[0];

    // Liste des patients du professionnel
    $statement = $em->getConnection()->prepare('EXEC WEBInfosListing @user_id=:user_id');
    $statement->bindValue('user_id', $this->getUser()->getId());
    $statement->execute();

    $liste_suivi = $statement->fetchAll();
    $flag = false;

    foreach($liste_suivi as $suivi){
      if($suivi['num_fiche'] == $patient['num_fiche']){
        $flag = true;
      }
    }

   if($flag == false){
     return $this->redirect($this->generateUrl('professionnel_index'));
   }

    return $this->render('AppBundle:Professionnel:patient.html.twig', [
        'suivi_vip' => $suivi_vip, 
        'suivis' => $suivis,
        'patient' => $patient,
        'id_patient' => $request->get('patient_id')
    ]);
    /*
     * VERSION POUR LES PROGRAMME D'ACCOMPAGNEMENT
    *
    $statement = $em->getConnection()->prepare('EXEC WEBAnalysesDetails @patient_id=:patient_id');
    $statement->bindValue('patient_id', $request->get('patient_id'));
    $statement->execute();
    $liste_analyses = $statement->fetchAll();

    $statement = $em->getConnection()->prepare('EXEC WEBDietDetails @patient_id=:patient_id');
    $statement->bindValue('patient_id', $request->get('patient_id'));
    $statement->execute();
    $liste_diet = $statement->fetchAll();

    $statement = $em->getConnection()->prepare('EXEC WEBEchanges @patient_id=:patient_id,@user_id=:user_id');
    $statement->bindValue('patient_id', $request->get('patient_id'));
    $statement->bindValue('user_id', $this->getUser()->getId());
    $statement->execute();
    $liste_echanges = $statement->fetchAll();

    $statement = $em->getConnection()->prepare('EXEC WEBPatientInfos @patient_id=:patient_id');
    $statement->bindValue('patient_id', $request->get('patient_id'));
    $statement->execute();
    $infos_patient = $statement->fetchAll();

    $patient = $infos_patient[0];

    $statement = $em->getConnection()->prepare('EXEC WEBPhaInfos @patient_id=:patient_id');
    $statement->bindValue('patient_id', $request->get('patient_id'));
    $statement->execute();
    $liste_phas = $statement->fetchAll();
    if(count($liste_phas)>0)
      $pha = $liste_phas[0];
    else
      $pha = array();

    return $this->render('AppBundle:Professionnel:patient.html.twig', [
        'actions'=>$liste_suivi_ps,
        'liste_diet'=>$liste_diet,
        'analyses'=>$liste_analyses,
        'echanges'=>$liste_echanges,
        'pha'=>$pha,
        'patient'=>$patient,
        'id_patient'=>$request->get('patient_id')
    ]);
    */
  }

  // -----------------------------------------------------------------------------------------------------------------

  public function questionnaireAction(Request $request)
  {

    $form = $this->get('form.factory')->create(QuestionnaireType::class, null);
    $form->handleRequest($request);

    $app_cle_captcha = $this->getParameter('cle_captcha');
    $isOk = false;
    // Soumission du formulaire
    if ($request->get("questionnaire"))
    {
      $datas = $request->get('questionnaire');

      $renderedLines = explode("\n", trim($this->render('AppBundle:Professionnel:send_info_inscription.html.twig', ['data' => $datas])));
      $body = implode("\n", $renderedLines);

      $message = \Swift_Message::newInstance()
          ->setSubject('Programme ACT4HER – Nouvelle demande de création de compte')
          ->setFrom($this->getParameter('mailer_from'), $this->getParameter('mailer_from_name'))
          ->setTo('act4her@test.com')
          ->setContentType('text/html')
          ->setBody($this->renderView('AppBundle:Professionnel:send_info_inscription.html.twig', ['data' => $datas]))
      ;

      if ( $this->get('mailer')->send($message) )
        $isOk = true;

    }

    return $this->render('AppBundle:Professionnel:questionnaire.html.twig', [
      'form' => $form->createView(),
      'app_cle_captcha' => $app_cle_captcha,
      'isOk' => $isOk
    ]);
  }

  // -----------------------------------------------------------------------------------------------------------------

  public function questionnairePatientAction(Request $request)
  {

    $form = $this->get('form.factory')->create(QuestionnairePatientType::class, null);
    $form->handleRequest($request);

    $isOk = false;
    $message = null;
    $id_doc = 0;

    $num_fiche_prescripteur = $this->getUser()->getProfessionnel()->getId();
    $sql = "EXEC WEBPSGetInfos @user_id = :user_id";

    $em = $this->getDoctrine()->getManager();

    $em->getConnection()->prepare('SET ANSI_NULLS ON')->execute();
    $em->getConnection()->prepare('SET ANSI_WARNINGS ON')->execute();
    $em->getConnection()->prepare('SET CONCAT_NULL_YIELDS_NULL ON')->execute();
    $em->getConnection()->prepare('SET ANSI_PADDING ON')->execute();
    $em->getConnection()->prepare('SET QUOTED_IDENTIFIER ON')->execute();

    $statement = $em->getConnection()->prepare($sql);
    $statement->bindValue('user_id', $this->getUser()->getId());
    $statement->execute();
    $results = $statement->fetchAll();
    $prescripteur = null;
    if(count($results)>0)
      $prescripteur = $results[0];

    if ($request->get("questionnaire_patient")) {
      $datas = $request->get('questionnaire_patient');

      $datas['num_fiche_ps'] = $num_fiche_prescripteur;

      $num_fiche_patient = 0;
      $naissance_patient = date_create_from_format('Y-m-d', $datas['naissance_patient']);
      $date_debut_traitement = date_create_from_format('Y-m-d', $datas['date_traitement']);
      $now = new \DateTime();

      $em = $this->getDoctrine()->getManager();

      $em->getConnection()->prepare('SET ANSI_NULLS ON')->execute();
      $em->getConnection()->prepare('SET ANSI_WARNINGS ON')->execute();
      $em->getConnection()->prepare('SET CONCAT_NULL_YIELDS_NULL ON')->execute();
      $em->getConnection()->prepare('SET ANSI_PADDING ON')->execute();
      $em->getConnection()->prepare('SET QUOTED_IDENTIFIER ON')->execute();

      $grade = 2;
      if(isset($datas['alerte3']) && $datas['alerte3'] == '1'){
         $grade = 3;
      }

      $alertePrs = 1;
      $alerteEquipe = 0;
      if(isset($datas['alerteEquipe']) && $datas['alerteEquipe'] == '1'){
        $alerteEquipe = 1;
        $alertePrs = 0;
      }

      $statement = $em->getConnection()->prepare('EXEC WEBPatientCreate 
      @nom_patient = :nom_patient,
      @prenom_patient = :prenom_patient,
      @adresse_patient = :adresse_patient,
      @cp_patient = :cp_patient,
      @ville_patient = :ville_patient,
      @mobile_patient = :mobile_patient,
      @email_patient = :email_patient,
      @civilite = :civilite,
      @date_naissance = :naissance_patient,
      @date_debut_traitement = :date_debut_traitement,
      @traitement = :traitement,
      @traitement_associe = :traitement_associe,
      @dosage = :dosage,
      @initiation = :initiation,
      @envoi_alerte_prescr = :envoi_alerte_prescr,
      @envoi_alerte_equipe = :envoi_alerte_equipe,
      @degre_alerte_ei     = :degre_alerte_ei ');

      $statement->bindValue('nom_patient', strtoupper($datas['nom_patient']));
      $statement->bindValue('prenom_patient', strtoupper($datas['prenom_patient']));
      $statement->bindValue('adresse_patient', $datas['adresse_patient']);
      $statement->bindValue('cp_patient', $datas['cp_patient']);
      $statement->bindValue('ville_patient', $datas['ville_patient']);
      $statement->bindValue('mobile_patient', $datas['mobile_patient']);
      $statement->bindValue('email_patient', $datas['email_patient']);
      $statement->bindValue('civilite', $datas['civilite']);
      $statement->bindValue('naissance_patient', $naissance_patient->format('Y-m-d'));
      $statement->bindValue('date_debut_traitement', $date_debut_traitement ? $date_debut_traitement->format('Y-m-d') : null);
      $statement->bindValue('traitement', $datas['traitement']);
      $statement->bindValue('traitement_associe', $datas['traitement_associe']);
      $statement->bindValue('dosage', $datas['dosage']);
      $statement->bindValue('initiation', $datas['initiation']);
      $statement->bindValue('envoi_alerte_prescr', $alertePrs);
      $statement->bindValue('envoi_alerte_equipe', $alerteEquipe);
      $statement->bindValue('degre_alerte_ei', $grade);


      $statement->execute();
      $result = $statement->fetchAll();

      $num_fiche_patient = $result[0]['num_fiche'];

      $datas['num_fiche'] = $num_fiche_patient;

      $num_fiche_patient = $result[0]['num_fiche'];
      //$num_fiche_patient = 1;
      $datas['num_fiche'] = $num_fiche_patient;

      if ($num_fiche_prescripteur != 0) {
        $sql = "INSERT INTO T_Professionnel ([num_fiche_patient],[num_fiche_prof],[type_cible])
        VALUES (:num_fiche_patient,:num_fiche_pds,1);";
        $statement = $em->getConnection()->prepare($sql);
        $statement->bindValue('num_fiche_pds', $num_fiche_prescripteur);
        $statement->bindValue('num_fiche_patient', $num_fiche_patient);
        $statement->execute();
        if(isset($datas['courrier']) && $datas['courrier']==1){
          $sql = "UPDATE T_Cible SET autorise_envoi_courrier_au_medecin = -1 WHERE num_fiche = :num_fiche_pds;";
          $statement = $em->getConnection()->prepare($sql);
          $statement->bindValue('num_fiche_pds', $num_fiche_prescripteur);
          $statement->execute();
        }
      }


      $directory = '/tmp/';
      $filename = 'consentement_patient_' . $num_fiche_patient . '.pdf';
      /*$pdf = $this
        ->get('templating')
        ->render('AppBundle:Professionnel:consentement.html.twig', [
          'request' => $request->request->all(),
          'datas' => $datas
        ]);
      //echo $pdf;
      //exit();
      $snappy = $this->container->get('knp_snappy.pdf');
      $options = [
        'margin-top' => 0,
        'margin-right' => 0,
        'margin-bottom' => 0,
        'margin-left' => 0,
      ];
      foreach ($options as $margin => $value) {
        $snappy->setOption($margin, $value);
      }

      $snappy->generateFromHtml($pdf, $directory . $filename, $options);*/

      //Generation PDF via remplissage champs
      require('/var/www/extranet_act4her/src/AppBundle/Resources/fpdm/fpdm.php');

      $initiation_non = $datas['initiation'] == 1 ? '' : 'X';
      $initiation_oui = $datas['initiation'] == 1 ? 'X' : '';

      $fields = array(
          'nom_patient'          => $datas['nom_patient'],
          'prenom_patient'       => $datas['prenom_patient'],
          'date_naissance'       => $naissance_patient->format('d/m/Y'),
          'tel_patient'          => $datas['mobile_patient'],
          'tel_patient1'         => $datas['mobile_patient'],
          'signature_patient'    => $datas['nom_patient'].' '.$now->format('d/m/Y'),
          'nom_prs'              => $datas['nom'],
          'prenom_prs'           => $datas['prenom'],
          'ville_cp_prs'         => $datas['cp'].' '.$datas['ville'],
          'adresse_prs'          => $datas['adresse'],
          'nom_tel_infirmier'    => $datas['nom_infirmier'].' '.$datas['tel_infirmier'],
          'debut_traitement'     => $date_debut_traitement ? $date_debut_traitement->format('d/m/Y') : '',
          'traitement'           => $datas['traitement'],
          'traitement_associe'   => $datas['traitement_associe'],
          'dosage'               => $datas['dosage'],
          'signature_prs'        => $datas['nom'].' '.$now->format('d/m/Y'),
          'initiation_oui'       => $initiation_oui,
          'initiation_non'       => $initiation_non,
          'pharmacie'            => $datas['pharmacie'],
          'alerte3_1'             => $grade == 3 ? 'X' : '',
          'alerteEquipe_1'        => $alerteEquipe == 1 ? 'X' : '',
          'emailEquipe_1'         => $datas['alerteMail'],


          'nom_patient2'          => $datas['nom_patient'],
          'prenom_patient2'       => $datas['prenom_patient'],
          'date_naissance2'       => $naissance_patient->format('d/m/Y'),
          'tel_patient2'          => $datas['mobile_patient'],
          'tel_patient22'         => $datas['mobile_patient'],
          'signature_patient2'    => $datas['nom_patient'].' '.$now->format('d/m/Y'),
          'nom_prs2'              => $datas['nom'],
          'prenom_prs2'           => $datas['prenom'],
          'ville_cp_prs2'         => $datas['cp'].' '.$datas['ville'],
          'adresse_prs2'          => $datas['adresse'],
          'nom_tel_infirmier2'    => $datas['nom_infirmier'].' '.$datas['tel_infirmier'],
          'debut_traitement2'     => $date_debut_traitement ? $date_debut_traitement->format('d/m/Y') : '',
          'traitement2'           => $datas['traitement'],
          'traitement_associe2'   => $datas['traitement_associe'],
          'dosage2'               => $datas['dosage'],
          'signature_prs2'        => $datas['nom'].' '.$now->format('d/m/Y'),
          'initiation_oui2'       => $initiation_oui,
          'initiation_non2'       => $initiation_non,
          'pharmacie2'            => $datas['pharmacie'],
          'alerte3_2'             => $grade == 3 ? 'X' : '',
          'alerteEquipe_2'        => $alerteEquipe == 1 ? 'X' : '',
          'emailEquipe_2'         => $datas['alerteMail'],

          'nom_patient3'          => $datas['nom_patient'],
          'prenom_patient3'       => $datas['prenom_patient'],
          'date_naissance3'       => $naissance_patient->format('d/m/Y'),
          'tel_patient3'          => $datas['mobile_patient'],
          'tel_patient33'         => $datas['mobile_patient'],
          'signature_patient3'    => $datas['nom_patient'].' '.$now->format('d/m/Y'),
          'nom_prs3'              => $datas['nom'],
          'prenom_prs3'           => $datas['prenom'],
          'ville_cp_prs3'         => $datas['cp'].' '.$datas['ville'],
          'adresse_prs3'          => $datas['adresse'],
          'nom_tel_infirmier3'    => $datas['nom_infirmier'].' '.$datas['tel_infirmier'],
          'debut_traitement3'     => $date_debut_traitement ? $date_debut_traitement->format('d/m/Y') : '',
          'traitement3'           => $datas['traitement'],
          'traitement_associe3'   => $datas['traitement_associe'],
          'dosage3'               => $datas['dosage'],
          'signature_prs3'        => $datas['nom'].' '.$now->format('d/m/Y'),
          'initiation_oui3'       => $initiation_oui,
          'initiation_non3'       => $initiation_non,
          'pharmacie3'            => $datas['pharmacie'],
          'alerte3_3'             => $grade == 3 ? 'X' : '',
          'alerteEquipe_3'        => $alerteEquipe == 1 ? 'X' : '',
          'emailEquipe_3'         => $datas['alerteMail'],
      );

      $pdf = new \FPDM('/var/www/extranet_act4her/web/pdf/consentement_clean.pdf');
      $pdf->Load($fields, true); //  false ISO-8859-1, true UTF-8
      $pdf->Merge();
      $pdf->Output('F',$directory.'/'.$filename);

      $data = fopen($directory . $filename, 'rb');
      $size = filesize($directory . $filename);
      $contents = fread($data, $size);
      fclose($data);

      $unpacked = unpack('H*hex', $contents);
      $encoded = '0x' . $unpacked['hex'];

      $em->getConnection()->prepare('SET ANSI_NULLS ON')->execute();
      $em->getConnection()->prepare('SET ANSI_WARNINGS ON')->execute();
      $em->getConnection()->prepare('SET CONCAT_NULL_YIELDS_NULL ON')->execute();
      $em->getConnection()->prepare('SET ANSI_PADDING ON')->execute();
      $em->getConnection()->prepare('SET QUOTED_IDENTIFIER ON')->execute();
      $em->getConnection()->exec("insert into T_Document_Log (num_fiche_pat,num_fiche_prof,[id_document],[nom_fichier],[fichier],[date_creation],[date_export])
        VALUES(" . $num_fiche_patient . "," . $num_fiche_prescripteur . ",1,'" . $filename . "'," . $encoded . ",getdate(),getdate())");
      $sql = "SELECT SCOPE_IDENTITY() AS [SCOPE_IDENTITY]";
      $statement = $em->getConnection()->prepare($sql);
      $statement->execute();
      $result = $statement->fetchAll();
      $id_doc = $result[0]['SCOPE_IDENTITY'];
      unlink($directory . $filename);

      $message = \Swift_Message::newInstance()
        ->setSubject('Programme ACT4HER - Nouvelle inscription en ligne')
        ->setFrom($this->getParameter('mailer_from'), $this->getParameter('mailer_from_name'))
        ->setTo('act4her@test.com')
        ->setContentType('text/html')
        ->setBody($this->renderView('AppBundle:Professionnel:send_info_inscription_patient.html.twig', ['data' => $datas]));

      /*$message2 = \Swift_Message::newInstance()
          ->setSubject('Apprentissage D�mo – Inscription enregistrée')
          ->setFrom($this->getParameter('mailer_from'), $this->getParameter('mailer_from_name'))
          ->setTo($datas['email'])
          ->setContentType('text/html')
          ->setBody($this->renderView('AppBundle:Professionnel:send_patient_inscription.html.twig', ['data' => $datas]));*/

      if ($this->get('mailer')->send($message) /*&& $this->get('mailer')->send($message2)*/){
        $isOk = true;

        $codes = $this
            ->getDoctrine()->getRepository('AppBundle:Code')
            ->findBy([
                'numero' => $datas['mobile_patient']
            ]);
        foreach($codes as $code){
          $em->remove($code);
          $em->flush();
        }
        $message = $this->renderView('AppBundle:Professionnel:validation.patient.html.twig', ['datas' => $datas, 'consentement_id_doc' => $id_doc]);

      }

      //return $this->redirect($this->generateUrl('app_questionnaire_patient_realise',['patient_id'=>$datas['num_fiche'], 'doc_id'=>$id_doc]));

    }

    return $this->render('AppBundle:Professionnel:questionnaire.patient.html.twig', [
      'form' => $form->createView(),
      'prs' =>$prescripteur,
      'isOk' => $isOk,
      'message' => $message
    ]);
  }

  // -----------------------------------------------------------------------------------------------------------------

  public function validationAction(Request $request)
  {
    return $this->render('AppBundle:Professionnel:validation.html.twig', []);
  }

  // -----------------------------------------------------------------------------------------------------------------

  public function validationPatientAction(Request $request)
  {
    $em = $this->getDoctrine()->getManager();
    $statement = $em->getConnection()->prepare("SELECT nom, prenom FROM T_Cible WHERE num_fiche = :num_fiche");
    $statement->bindValue('num_fiche', $request->get('patient_id'));
    $statement->execute();
    $result = $statement->fetchAll();
    $patient = $result[0];
    $consentement_id_doc = $request->get('doc_id');
    return $this->render('AppBundle:Professionnel:validation.patient.html.twig', ['nom'=>$patient['nom'], 'consentement_id_doc'=>$consentement_id_doc]);
  }

  // -----------------------------------------------------------------------------------------------------------------

  public function checkAction(Request $request)
  {
    $output = [
        'has_error'   => false,
        'message' => ''
    ];

    if ($request->isXmlHttpRequest()) {
      $data = null;

      $professionnel_emails = $this
          ->getDoctrine()->getRepository('AppBundle:Professionnel')
          ->findBy([
              'email' => $request->get('email')
          ]);

      $professionnel_rpps = null;
      if($request->get('rpps') != ''){
        $professionnel_rpps = $this
            ->getDoctrine()->getRepository('AppBundle:Professionnel')
            ->findBy([
                'rpps' => $request->get('rpps')
            ]);
      }


      if (count($professionnel_emails) > 0 || count($professionnel_rpps) > 0) {

        $message = 'N°Rpps ou Email déjà utilisé';
        if (count($professionnel_emails) > 0 && count($professionnel_rpps) > 0) {
          $message = "L'adresse email et le N°Rpps ont déjà été enregistrés. Veuillez utiliser d'autres informations.";
        }elseif(count($professionnel_emails) > 0 ){
          $message = "L'adresse email a déjà été enregistré. Veuillez utiliser une autre adresse";
        }else{
          $message = "Le N°Rpps a déjà été enregistré. Veuillez utiliser un autre numéro.";
        }

        $output = [
            'has_error'   => true,
            'message' => $message
        ];
      }
    }

    return new JsonResponse($output);
  }

  // -----------------------------------------------------------------------------------------------------------------

  public function envoiCodeAction(Request $request)
  {
    $code = rand(10000,99999);

    $em = $this->getDoctrine()->getManager();

    $dbCode = new Code();
    $dbCode->setCode($code);
    $dbCode->setNumero($request->get('mobile'));
    $dbCode->setDate(new \DateTime);

    $em->persist($dbCode);
    $em->flush();


    $mobile = $request->get('mobile');
    $message = \Swift_Message::newInstance()
      ->setSubject('')
      ->setFrom('act4her@test.com')
      ->setTo($mobile.'@echoemail.net')
      ->setContentType('text/html')
      ->setBody($this->renderView('AppBundle:Professionnel:send_informations.html.twig', ['code' => $code]))
    ;


    if ( $this->get('mailer')->send($message) )
      $text = array('envoi' => true, 'code' => 'ok');
    else
      $text = array('envoi' => false);

    return new JsonResponse($text);

  }

  // -----------------------------------------------------------------------------------------------------------------

  public function checkCodeAction(Request $request)
  {

    $codes = $this
        ->getDoctrine()->getRepository('AppBundle:Code')
        ->findBy([
            'code' => $request->get('code'),
            'numero' => $request->get('mobile'),
        ]);

    $dateNow = new \DateTime();
    $dateNow->modify('-20 minutes');
    $codeCorrect = false;

    foreach($codes as $code){
      if($dateNow < $code->getDate()){
        $codeCorrect = true;
      }
    }

    if($codeCorrect){
      $text = array('envoi' => true);
    }else{
      $text = array('envoi' => false);
    }

    return new JsonResponse($text);

  }

  // -----------------------------------------------------------------------------------------------------------------

  public function testDoublonAction(Request $request)
  {

    $nom = $request->get('nom_patient');
    $cp = $request->get('cp_patient');

    $sql = "SELECT COUNT(*) as nb_fiche FROM T_Cible WHERE T_Cible.nom = :nom AND T_Cible.CodePostal = :cp ";

    $em = $this->getDoctrine()->getManager();
    $statement = $em->getConnection()->prepare($sql);
    $statement->bindValue('nom', $nom);
    $statement->bindValue('cp', $cp);
    $statement->execute();
    $result = $statement->fetchAll();

    if ($result[0]['nb_fiche'] == 0)
      $text = array('doublon' => false);
    else
      $text = array('doublon' => true);

    $output = [
        'has_error'   => false,
        'message' => $text
    ];
    return new JsonResponse($output);

  }

  // -----------------------------------------------------------------------------------------------------------------

  public function downloadQuestionnaireAction(Request $request)
  {

    $em = $this->getDoctrine()->getManager();

    $em->getConnection()->prepare('SET ANSI_NULLS ON')->execute();
    $em->getConnection()->prepare('SET ANSI_WARNINGS ON')->execute();
    $em->getConnection()->prepare('SET CONCAT_NULL_YIELDS_NULL ON')->execute();
    $em->getConnection()->prepare('SET ANSI_PADDING ON')->execute();
    $em->getConnection()->prepare('SET QUOTED_IDENTIFIER ON')->execute();

    $statement = $em->getConnection()->prepare('select * from T_Document_Log where id_doc_log=:id_doc_log');
    $statement->bindValue('id_doc_log', $request->get('doc_id'));
    $statement->execute();
    $query = $statement->fetchAll();

    // Liste des patients du professionnel
    $statement = $em->getConnection()->prepare('EXEC WEBInfosListingForDoc @user_id=:user_id');
    $statement->bindValue('user_id', $this->getUser()->getId());
    $statement->execute();

    $liste_suivi = $statement->fetchAll();
    $flag = false;

    foreach($liste_suivi as $suivi){
      if($suivi['num_fiche'] == $query[0]['num_fiche_pat']){
        $flag = true;
      }
    }

    if($flag == false){
      $output = [
          'has_error'   => false,
          'message' => "Vous n'avez pas les droits pour lire ce document"
      ];
      return new JsonResponse($output);
    }

    if(count($query) == 0){
      $output = [
          'has_error'   => false,
          'message' => 'Error'
      ];
      return new JsonResponse($output);
    }else{
      foreach ($query as $row) {
        $statement = $em->getConnection()->prepare('EXEC WEBPSGetInfos @user_id=:user_id');
        $statement->bindValue('user_id', $this->getUser()->getId());
        $statement->execute();
        $liste = $statement->fetchAll();
        /*if(count($liste) > 0) {
          $statement = $em->getConnection()->prepare('INSERT INTO T_Lecture_Document VALUES (GETDATE(),:prs_id, :id_doc_log)');
          $statement->bindValue('prs_id', $liste[0]['ps_num_fiche']);
          $statement->bindValue('id_doc_log', $request->get('doc_id'));
          $statement->execute();
        }*/
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-type:application/pdf");//application/octet-stream
        header("Content-Disposition:attachment;filename=" .$row['nom_fichier']);
        header("Content-Transfer-Encoding: binary");
        echo $row['fichier'];
      }
    }
  }

  public function downloadDocumentAction(Request $request)
  {

    $em = $this->getDoctrine()->getManager();

    $em->getConnection()->prepare('SET ANSI_NULLS ON')->execute();
    $em->getConnection()->prepare('SET ANSI_WARNINGS ON')->execute();
    $em->getConnection()->prepare('SET CONCAT_NULL_YIELDS_NULL ON')->execute();
    $em->getConnection()->prepare('SET ANSI_PADDING ON')->execute();
    $em->getConnection()->prepare('SET QUOTED_IDENTIFIER ON')->execute();

    $statement = $em->getConnection()->prepare('select * from T_Document_Log where id_doc_log=:id_doc_log');
    $statement->bindValue('id_doc_log', $request->get('doc_id'));
    $statement->execute();
    $query = $statement->fetchAll();
    if(count($query) == 0){
      $output = [
          'has_error'   => false,
          'message' => 'Error'
      ];
      return new JsonResponse($output);
    }else{
      foreach ($query as $row) {
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-type:application/octet-stream");
        header("Content-Disposition:attachment;filename=" .$row['nom_fichier']);
        header("Content-Transfer-Encoding: binary");
        echo $row['fichier'];
      }
    }
  }

  // -----------------------------------------------------------------------------------------------------------------

  public function downloadFormulaireAction(Request $request)
  {

    // get the HTML
    ob_start();

    $em = $this->getDoctrine()->getManager();

    $patient_id = $request->get('patient_id');

    $statement = $em->getConnection()->prepare('EXEC WEBPatientGet @patient_id=:patient_id');
    $statement->bindValue('patient_id', $patient_id);
    $statement->execute();
    $infos_patient = $statement->fetchAll();

    $statement = $em->getConnection()->prepare('EXEC WEBPatientGetTraitant @patient_id=:patient_id');
    $statement->bindValue('patient_id', $patient_id);
    $statement->execute();
    $traitant = $statement->fetchAll();


    $directory 	= '/tmp/';
    $filename 	= $this->getUniqueFileName('consentement.pdf', $directory);
    $pdf = $this->render('AppBundle:Professionnel:consentement.html.twig', [
        'infos_patient'=>$infos_patient[0],
        'traitant'=>$traitant[0]
    ]);

    $fullname	=	 $directory . '/' . $filename;
    $filePath = $directory . $filename;
    $this->get('knp_snappy.pdf')->generateFromHtml($pdf, $fullname);

    // Prepare BinaryFileResponse
    $response = new BinaryFileResponse($filePath);
    $response->trustXSendfileTypeHeader();

    return $response->setContentDisposition(
        ResponseHeaderBag::DISPOSITION_ATTACHMENT,
        basename($filename),
        iconv('UTF-8', 'ASCII//TRANSLIT', basename($filename))
    );
    //return JsonResponse(['document'=>$document,'attachement'=>$fullname]);
  }

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

  public function addFileAction(Request $request){

    $em = $this->getDoctrine()->getManager();
    $id_patient  = $request->get('patient');
    /*if(isset($_FILES['file']['name'])){

      $filename = $_FILES['file']['name'];

      $location = "upload/".$filename;
      $imageFileType = pathinfo($location,PATHINFO_EXTENSION);
      $imageFileType = strtolower($imageFileType);

      $valid_extensions = array("jpg","jpeg","png");

      $response = 0;
      if(in_array(strtolower($imageFileType), $valid_extensions)) {
        if(move_uploaded_file($_FILES['file']['tmp_name'],$location)){
          $response = $location;
        }
      }

      echo $response;
      exit;
    }*/
    $data = fopen($request->files->get('new_doc_doc')->getPathName(), 'rb');
    $size = filesize($request->files->get('new_doc_doc')->getPathName());
    $contents = fread($data, $size);
    fclose($data);

    $comm = '';
    if($request->get('new_doc_comm') && $request->get('new_doc_comm') != "")
      $comm = $request->get('new_doc_comm');

    $filename = $request->files->get('new_doc_doc')->getClientOriginalName();

    $unpacked = unpack('H*hex', $contents);
    $encoded =  '0x' . $unpacked['hex'];

    $statement = $em->getConnection()->prepare("insert into T_Document_Log (num_fiche_pat,[id_document],[nom_fichier],[fichier],[commentaire][date_creation])
            VALUES(:num_fiche,3,:nom_fichier,".$encoded.",:comm ,getdate())");
    $statement->bindValue('num_fiche', $id_patient);
    $statement->bindValue('nom_fichier', $filename);
    $statement->bindValue('comm', $comm);
    $statement->execute();

    $sql = "SELECT SCOPE_IDENTITY() AS [SCOPE_IDENTITY]";
    $statement = $em->getConnection()->prepare($sql);
    $statement->execute();
    $result = $statement->fetchAll();
    $id_doc_log_photo = $result[0]['SCOPE_IDENTITY'];

    $statement = $em->getConnection()->prepare("SELECT nom, prenom FROM T_Cible WHERE num_fiche = :num_fiche");
    $statement->bindValue('num_fiche', $id_patient);
    $statement->execute();
    $result = $statement->fetchAll();
    $result[0]['id'] = $id_patient;
    $patient = $result[0];

    $message = \Swift_Message::newInstance()
        ->setSubject('Programme ACT4HER – Dépôt document prescripteur')
        ->setFrom($this->getParameter('mailer_from'), $this->getParameter('mailer_from_name'))
        ->setTo('act4her@test.com')
        ->setContentType('text/html')
        ->setBody($this->renderView('AppBundle:Professionnel:send_doc_add.html.twig', ['patient' => $patient, 'commentaire'=>$comm]))
    ;
    if($this->get('mailer')->send($message)){
      $id_doc_log = $id_doc_log_photo;
    }else{
      $id_doc_log = null;
    }

    /**/
    return JsonResponse(['document'=>$id_doc_log_photo]);
  }
  // -----------------------------------------------------------------------------------------------------------------

  /**
   *
   * @param Request $request
   * @return \Symfony\Component\HttpFoundation\Response
   */

  public function questionnairePapierAction(Request $request,$token=null) {

    $em = $this->getDoctrine()->getManager();
    $em->getConnection()->prepare('SET ANSI_NULLS ON')->execute();
    $em->getConnection()->prepare('SET ANSI_WARNINGS ON')->execute();
    $em->getConnection()->prepare('SET CONCAT_NULL_YIELDS_NULL ON')->execute();
    $em->getConnection()->prepare('SET ANSI_PADDING ON')->execute();

    $toogle = [
        ['item' => 'q5',  'check' => ['Autre'],  'parse' => ['tr_q5_autre']],
        ['item' => 'q7',  'check' => ['Oui'],  'parse' => ['tr_q8']],
        ['item' => 'q15',  'check' => ['Oui'],  'parse' => ['tr_q16']],
    ];
    $json_toggle = json_encode($toogle);

    $type_suivi = null;

    if ($token != null){

      $statement = $em->getConnection()->prepare('
        SELECT [id_parcours],[num_fiche],[web_s02_token],[web_s04_token],[web_m03_token],[web_m06_token],[web_m12_token] FROM [T_Parcours]
        WHERE actif=-1 AND ([web_s02_token] = :token OR [web_s04_token] = :token OR [web_m03_token] = :token OR [web_m06_token] = :token OR [web_m12_token] = :token)');
      $statement->bindValue('token', $token);
      $statement->execute();
      $results = $statement->fetchAll();

      foreach($results as $r){
        $num_fiche = $r['num_fiche'];
        $id_parcours = $r['id_parcours'];

        $statement = $em->getConnection()->prepare('SELECT *  FROM [T_Script_AQ] WHERE suivi_cadence = :type_suivi AND num_fiche = :num_fiche  AND id_parcours = :id_parcours ');
        $statement->bindValue('num_fiche', $num_fiche);
        $statement->bindValue('id_parcours', $id_parcours);
        $previous_suivi = null;
        if($r['web_s02_token'] == $token){
          $type_suivi = "s02";
        }
        if($r['web_s04_token'] == $token){
          $previous_suivi = "s02";
          $type_suivi = "s04";
        }
        if($r['web_m03_token'] == $token){
          $previous_suivi = "s04";
          $type_suivi = "m03";
        }
        if($r['web_m06_token'] == $token){
          $previous_suivi = "m03";
          $type_suivi = "m06";
        }
        if($r['web_m12_token'] == $token){
          $previous_suivi = "m06";
          $type_suivi = "m12";
        }
        $statement->bindValue('type_suivi', $type_suivi);

        $statement->execute();
        $reponses = $statement->fetchAll();
        if(isset($reponses[0]) && $reponses[0]['fichier_genere_prescr'] != null && $reponses[0]['date_envoi_prescr'] != null)
          return $this->render ( 'AppBundle:Professionnel:confirmation.html.twig' );
      }
    }
    if(!$type_suivi){
      return $this->render('AppBundle:Professionnel:forbidden.html.twig', []);
    }else{
      if ($request->get("form")) {

        $datas = $request->get('form');

        if(count($reponses)>0){
          $sql = "UPDATE T_Script_AQ SET [date_realisation] = GETDATE()
            ,[poids]=:q1
            ,[taille]=:q2
            ,[bilan_bio_date_prochain]=:q3
            ,[traitement_depuis_dern_appel]=:q4
            ,[condition_usage_semaine_passee]=:q5
            ,[obs_tttnonpris_oubli]=:q6
            ,[obs_tttnonpris_autrecause]=:q7
            ,[obs_tttnonpris_cause]=:q8
            ,[obs_tttnonpris_cause_comm]=:q8_autre
            ,[obs_modif_dose]=:q9
            ,[obs_oubli_emporter]=:q10
            ,[obs_medic_pris_aujourdhui]=:q11
            ,[obs_arret_medic_si_pas_symptome]=:q12
            ,[obs_inconvenient_respect_ttt]=:q13
            ,[obs_rappel_difficile]=:q14
            ,[obs_score]=:obs_score
            ,[obs_alerte]=:obs_alerte
            ,[tolerance_alerte]=:tolerance_alerte
            ,[tolerance_signalement]=:tolerance_signalement
            ,[tolerance_grade]=:q16
            ,[tolerance_comm]=:q15
            ,[qualvie_alerte]=:qualvie_alerte
            ,[qualvie_mobilite]=:q17
            ,[qualvie_autonomie]=:q18
            ,[qualvie_activite]=:q19
            ,[qualvie_douleur]=:q20
            ,[qualvie_anxiete]=:q21
            ,[qualvie_echelle_etatsante]=:q22
            ,[satisf_note_satisfaction]=:q23
            ,[satisf_aide_relation_oncologue]=:q24
            ,[satisf_aide_comprehension_maladie]=:q25
            ,[satisf_aide_prise_traitement]=:q26
            ,[satisf_commentaire]=:q27

            WHERE suivi_cadence = :suivi_cadence AND num_fiche = :num_fiche  AND id_parcours = :id_parcours;";
          $statement = $em->getConnection()->prepare($sql);
        }else {
          $sql = "INSERT INTO T_Script_AQ ([suivi_cadence],[suivi_nom],[num_fiche],[id_parcours],[date_realisation],[poids],[taille],[bilan_bio_date_prochain],[traitement_depuis_dern_appel],[condition_usage_semaine_passee]
          ,[obs_tttnonpris_oubli],[obs_tttnonpris_autrecause],[obs_tttnonpris_cause],[obs_tttnonpris_cause_comm],[obs_modif_dose],[obs_oubli_emporter],[obs_medic_pris_aujourdhui]
          ,[obs_arret_medic_si_pas_symptome],[obs_inconvenient_respect_ttt],[obs_rappel_difficile],[obs_score],[obs_alerte]
          ,[tolerance_alerte],[tolerance_signalement],[tolerance_grade],[tolerance_comm]
          ,[qualvie_mobilite],[qualvie_autonomie],[qualvie_activite],[qualvie_douleur],[qualvie_anxiete],[qualvie_echelle_etatsante],[qualvie_alerte]
          ,[satisf_note_satisfaction],[satisf_aide_relation_oncologue],[satisf_aide_comprehension_maladie],[satisf_aide_prise_traitement],[satisf_commentaire]
          )
          VALUES (:suivi_cadence,:suivi_nom,:num_fiche,:id_parcours,GETDATE(),:q1,:q2,:q3,:q4,:q5
          ,:q6,:q7,:q8,:q8_autre,:q9,:q10,:q11
          ,:q12,:q13,:q14,:obs_score,:obs_alerte
          ,:tolerance_alerte,:tolerance_signalement,:q16,:q15
          ,:q17,:q18,:q19,:q20,:q21,:q22,:qualvie_alerte
          ,:q23,:q24,:q25,:q26,:q27
          );";
          $statement = $em->getConnection()->prepare($sql);
          $statement->bindValue('suivi_nom', strtoupper(str_replace('0','',$type_suivi)));
        }
        $statement->bindValue('suivi_cadence', $type_suivi);
        $statement->bindValue('num_fiche', $num_fiche);
        $statement->bindValue('id_parcours', $id_parcours);

        $statement->bindValue('obs_score', isset($datas['obs_score'])?$datas['obs_score']:null);
        if(isset($datas['obs_score']) && $datas['obs_score']<6)
          $statement->bindValue('obs_alerte', -1);
        else
          $statement->bindValue('obs_alerte', 0);

        if(isset($datas['question_q15']) && $datas['question_q15']!='' && $datas['question_q15']!='Non')
          $statement->bindValue('tolerance_signalement', 'Oui');
        else
          $statement->bindValue('tolerance_signalement', 'Non');

        if(isset($datas['tolerance_score']) && $datas['tolerance_score']>0)
          $statement->bindValue('tolerance_alerte', -1);
        else
          $statement->bindValue('tolerance_alerte', 0);

        $score_q22 = isset($datas['question_q22'])?$datas['question_q22']:'';
        $old_score_q22='';

        if($previous_suivi){
          $statement2 = $em->getConnection()->prepare('SELECT * FROM [T_Script_AQ] WHERE suivi_cadence = :type_suivi AND num_fiche = :num_fiche  AND id_parcours = :id_parcours ');
          $statement2->bindValue('num_fiche', $num_fiche);
          $statement2->bindValue('id_parcours', $id_parcours);
          $statement2->bindValue('type_suivi', $previous_suivi);
          $statement2->execute();
          $old = $statement2->fetchAll();
          $old_score_q22 =  isset($old[0]['qualvie_echelle_etatsante'])?$old[0]['qualvie_echelle_etatsante']:'';
        }
        if((isset($datas['question_q17_score']) && $datas['question_q17_score']==1)
            || (isset($datas['question_q18_score']) && $datas['question_q18_score']==1)
            || (isset($datas['question_q19_score']) && $datas['question_q19_score']==1)
            || (isset($datas['question_q20_score']) && $datas['question_q20_score']==1)
            || (isset($datas['question_q21_score']) && $datas['question_q21_score']==1)
            || ($old_score_q22!=''&&$score_q22!=''&& abs($score_q22-$old_score_q22)>20)
        )
          $statement->bindValue('qualvie_alerte', -1);
        else
          $statement->bindValue('qualvie_alerte', 0);

        $statement->bindValue('q1', isset($datas['question_q1'])?$datas['question_q1']:'');
        $statement->bindValue('q2', isset($datas['question_q2'])?$datas['question_q2']:'');
        $statement->bindValue('q3', isset($datas['question_q3'])&& $datas['question_q3']!=""?date_create_from_format('Y-m-d',$datas['question_q3'])->format('Ymd'):null);
        $statement->bindValue('q4', isset($datas['question_q4'])?$datas['question_q4']:null);
        $statement->bindValue('q5', isset($datas['question_q5']) ? $datas['question_q5']=='Autre'?$datas['question_q5_autre']:$datas['question_q5']:null);

        $statement->bindValue('q6', isset($datas['question_q6'])?$datas['question_q6']:null);
        $statement->bindValue('q7', isset($datas['question_q7'])?$datas['question_q7']:null);
        $statement->bindValue('q8', isset($datas['question_q8'])?$datas['question_q8']:null);
        $statement->bindValue('q8_autre', isset($datas['question_q8_autre'])?$datas['question_q8_autre']:null);
        $statement->bindValue('q9', isset($datas['question_q9'])?$datas['question_q9']:null);
        $statement->bindValue('q10', isset($datas['question_q10'])?$datas['question_q10']:null);
        $statement->bindValue('q11', isset($datas['question_q11'])?$datas['question_q11']:null);

        $statement->bindValue('q12', isset($datas['question_q12'])?$datas['question_q12']:null);
        $statement->bindValue('q13', isset($datas['question_q13'])?$datas['question_q13']:null);
        $statement->bindValue('q14', isset($datas['question_q14'])?$datas['question_q14']:null);

        $statement->bindValue('q15', isset($datas['question_q15'])?$datas['question_q15']:null);
        $statement->bindValue('q16', isset($datas['question_q16'])?$datas['question_q16']:null);

        $statement->bindValue('q17', isset($datas['question_q17'])?$datas['question_q17']:null);
        $statement->bindValue('q18', isset($datas['question_q18'])?$datas['question_q18']:null);
        $statement->bindValue('q19', isset($datas['question_q19'])?$datas['question_q19']:null);
        $statement->bindValue('q20', isset($datas['question_q20'])?$datas['question_q20']:null);
        $statement->bindValue('q21', isset($datas['question_q21'])?$datas['question_q21']:null);
        $statement->bindValue('q22', isset($datas['question_q22'])?$datas['question_q22']:null);

        $statement->bindValue('q23', isset($datas['question_q23'])?$datas['question_q23']:null);
        $statement->bindValue('q24', isset($datas['question_q24'])?$datas['question_q24']:null);
        $statement->bindValue('q25', isset($datas['question_q25'])?$datas['question_q25']:null);
        $statement->bindValue('q26', isset($datas['question_q26'])?$datas['question_q26']:null);
        $statement->bindValue('q27', isset($datas['question_q27'])?$datas['question_q27']:null);

        $statement->execute();
//print_r($datas); exit();
        if("s02" == $type_suivi){
          $sql = "web_s02_date = GETDATE()";
        }
        if("s04" == $type_suivi){
          $sql = "web_s04_date = GETDATE()";
        }
        if("m03" == $type_suivi){
          $sql = "web_m03_date = GETDATE()";
        }
        if("m06" == $type_suivi){
          $sql = "web_m06_date = GETDATE()";
        }
        if("m12" == $type_suivi){
          $sql = "web_m12_date = GETDATE()";
        }
        $statement = $em->getConnection()->prepare('UPDATE T_Parcours SET '.$sql.' WHERE [id_parcours] = :id_parcours ');
        $statement->bindValue('id_parcours', $id_parcours);
        $statement->execute();


        $statement = $em->getConnection()->prepare('SELECT *  FROM [T_Cible] WHERE num_fiche = :num_fiche');
        $statement->bindValue('num_fiche', $num_fiche);
        $statement->execute();
        $reponses = $statement->fetchAll();

        $message = \Swift_Message::newInstance()
            ->setSubject('Programme ACT4HER – AQ complété')
            ->setFrom($this->getParameter('mailer_from'), $this->getParameter('mailer_from_name'))
            ->setTo('act4her@test.com')
            ->setContentType('text/html')
            ->setBody($this->renderView('AppBundle:Professionnel:send_info_pv.html.twig', ['data' => $reponses[0]]))
        ;
        //$this->get('mailer')->send($message);


        $statement = $em->getConnection()->prepare('EXEC WEBScoresAQ @num_fiche=:num_fiche, @suivi_cadence=:suivi_cadence, @id_parcours=:id_parcours');
        $statement->bindValue('suivi_cadence', $type_suivi);
        $statement->bindValue('num_fiche', $num_fiche);
        $statement->bindValue('id_parcours', $id_parcours);
        //$statement->execute();

        return $this->render ( 'AppBundle:Professionnel:confirmation.html.twig' );
      }

      return $this->render ( 'AppBundle:Professionnel:questionnaire.extranet.html.twig', [
          'request' => $request,
          'reponses' => isset($reponses[0])?$reponses[0]:array(),
          'type_suivi' => $type_suivi,
          'toggle' => $json_toggle
      ] );

    }

  }

  public function preambuleAction(Request $request)
  {
    return $this->render('AppBundle:Professionnel:preambule.html.twig',[]);
  }
}
