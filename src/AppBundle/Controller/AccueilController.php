<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\HttpFoundation\JsonResponse;

use AppBundle\Form\QuestionnaireType;

use AppBundle\Entity\Professionnel;

class AccueilController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        //return $this->redirect($this->generateUrl('professionnel_index', []));
        return $this->render('AppBundle:Accueil:index.html.twig', []);
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Accès à la page des documents
     */
    public function mentionsLegalesAction(Request $request)
    {
        return $this->render('AppBundle:Accueil:mentions.legales.html.twig');
    }

    /**
     * Accès à la page des documents
     */
    public function mentionsLegalesConsentAction(Request $request)
    {
        return $this->render('AppBundle:Accueil:mentions.legales.consent.html.twig');
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function validationAction(Request $request)
    {

        return $this->render('AppBundle:Accueil:validation.html.twig', [
        ]);
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

            $professionnel_rppss = null;
            if($request->get('rpps') != ''){
                $professionnel_rppss = $this
                    ->getDoctrine()->getRepository('AppBundle:Professionnel')
                    ->findBy([
                        'rpps' => $request->get('rpps')
                    ]);
            }


            if (count($professionnel_emails) > 0 || count($professionnel_rppss) > 0) {

                $message = 'N°Rpps ou Email déjà utilisé';
                if (count($professionnel_emails) > 0 && count($professionnel_rppss) > 0) {
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

}
