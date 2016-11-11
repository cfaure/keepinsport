<?php

namespace Ks\PaymentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

/**
 * 
 */
class PaylineController extends Controller
{

    /**
     * Appelé via $PBX_DONE
     * 
     * @Route("/post-process-done", name = "ksPayment_postProcessDone")
     */
    public function postProcessDone()
    {
        return $this->render('KsPaymentBundle:Payline:done.html.twig', array(
            //
        ));
    }
    
    /**
     * Appelé via $PBX_REFUSE
     * 
     * @Route("/post-process-deny", name = "ksPayment_postProcessDeny")
     */
    public function postProcessDeny()
    {
        return $this->render('KsPaymentBundle:Payline:deny.html.twig', array(
            //
        ));
    }
    
    /**
     * Appelé via $PBX_ANNULE
     * 
     * @Route("/post-process-cancel", name = "ksPayment_postProcessCancel")
     */
    public function postProcessCancel()
    {
        return $this->render('KsPaymentBundle:Payline:cancel.html.twig', array(
            //
        ));
    }
    
    /**
     * Appelé via $PBX_ATTENTE
     * 
     * @Route("/post-process-standby", name = "ksPayment_postProcessStandby")
     */
    public function postProcessStandby()
    {
        return $this->render('KsPaymentBundle:Payline:standby.html.twig', array(
            //
        ));
    }
    
    /**
     * Action appellée dans le process IPN de Paybox system.
     * C'est un appel serveur à serveur et c'est le SEUL moyen de savoir si le paiement a été vraiment validé ou pas.
     * Cette action sera toujours et systématiquement appelée lors de chaque tentative de paiement.
     * 
     * En cas d'erreur dans cette action un mail de warning sera envoyé sur l'adresse mail configurée dans
     * l'admin paybox (clfaure@gmail.com)
     * Si tout se passe bien on retourne une page html vide avec le code 200.
     * 
     * @Route("/validate", name = "ksPayment_validate", options={"expose"=true}  )
     * @Template()
     */
    public function validateAction()
    {
        $response = new Response();
        
        try {
            $request    = $this->get('request');
            $em         = $this->getDoctrine()->getEntityManager();
            $orderRepo  = $em->getRepository('KsPaymentBundle:Order');
            $parameters = $request->query->all();

            $error  = $request->get('erreur');
            $refId  = $request->get('ref', '');
            
            if ($refId == '') {
                throw new \Exception('Référence de paiement non trouvée. Impossible de valider le paiement.');
            }

            $order = $orderRepo->findOneById($refId);
            if (empty($order)) {
                throw new \Exception('Enregistrement ks_order non trouvé. Impossible de valider le paiement.');
            }
                        
            // TODO: à améliorer, décrypter les différents codes d'erreurs
            $status = $error === '00000' ? 'valid' : 'error';
            // TODO: valider la signature fournie par paybox
            // TODO: valider l'ip de paybox
            
            if ($status == 'valid') {
                // TODO: c'est ici qu'on doit valider le changement de statut de l'utilisateur
                // on peut récupérer la commande effectuée dans l'objet $order
            }
            
            $order->setStatus($status);
            $order->setPayboxAnswer(serialize($parameters));
            $order->setUpdatedAt(new \DateTime());
            $em->persist($order);
            $em->flush();
        
            // Doit renvoyer une page HTML vide
            $response->setStatusCode(200);
        } catch (\Exception $e) { // catch all
            // TODO: envoi d'un mail d'erreur à flo et clem avec le détail de l'exception
            // FIXME: à tester en preprod, a priori la réponse doit toujours être vide.
            $response->setContent($e->getMessage());
            $response->setStatusCode(500);
        }
        
        $response->headers->set('Content-Type', 'text/html');
        
        return $response;
    }
}