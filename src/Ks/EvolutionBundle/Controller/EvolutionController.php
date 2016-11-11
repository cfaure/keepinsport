<?php
namespace Ks\EvolutionBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/*Pour executer une commande du controller*/
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class EvolutionController extends Controller
{
    /**
     * @Route("/", name = "KsEvolution_list", options={"expose"=true} )
     * @Template()
     */
    public function evolutionAction()
    {
        $em                   = $this->getDoctrine()->getEntityManager();
        $user                 = $this->get('security.context')->getToken()->getUser();
        $evolutionRep         = $em->getRepository('KsEvolutionBundle:Evolution');
        
        $evolutions           = $evolutionRep->getEvolutionsWithUserHasVotedAndNumVotes($user);
        
        $session = $this->get('session');
        $session->set('pageType', 'wikisport');
        $session->set('page', 'evolutionList');
        
        return array(
            "evolutions"        => $evolutions
        );
    }
    
    /**
     * 
     * @Route("/voteOnEvolution/{evolutionId}", requirements={"evolutionId" = "\d+"}, name = "ksEvolution_voteOnEvolution", options={"expose"=true} )
     * @param int $evolutionId
     */
    public function voteOnEvolutionAction($evolutionId)
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $evolutionRep   = $em->getRepository('KsEvolutionBundle:Evolution');
        $user           = $this->get('security.context')->getToken()->getUser();
        
        //Services
        //$notificationService   = $this->get('ks_notification.notificationService');
        
        $evolution = $evolutionRep->find($evolutionId);
        
        if (!is_object($evolution) ) {
            $impossibleEvolutionMsg = $this->get('translator')->trans('impossible-to-find-evolution-%evolutionId%', array('%evolutionId%' => $evolutionId));
            throw new AccessDeniedException($impossibleEvolutionMsg);
        }
        
        $numVotesByUser = (int)$evolutionRep->getNumVotesByUser($user);
        
        $responseDatas = array();
        
        //Si l'utilisateur n'a pas déjà voté sur l'activité et pas atteint son maximum de votes
        if ( ! $evolutionRep->haveAlreadyVoted($evolution, $user) && $numVotesByUser <3) {
            $evolutionRep->voteOnEvolution($evolution, $user);

            //Création d'une notification
//            $notificationType_name = "vote";
//            $notificationType = $em->getRepository('KsNotificationBundle:NotificationType')->findOneByName($notificationType_name);
//
//            if (!$notificationType) {
//                $impossibleNotificationTypeMsg = $this->get('translator')->trans('impossible-to-find-notification-%$notificationTypeName%', array('%$notificationTypeName%' => $notificationType_name));
//                throw $this->createNotFoundException($impossibleNotificationTypeMsg);
//            }
//            
//            if ($evolution->getUser() != $user) {
//                $notificationService->sendNotification($evolution, $user, $evolution->getUser(), $notificationType_name);  
//            }
//            
//            //Une notification de commentaire pour chaque abonné
//            foreach($evolution->getSubscribers() as $evolutionHasSubscribers) {
//                $subscriber = $evolutionHasSubscribers->getSubscriber();
//
//                //Si l'abonné n'est pas lui même et qu'il n'a pas posté l'activité
//                if ($subscriber != $user && $evolution->getUser() != $subscriber) {
//                    $notificationService->sendNotification($evolution, $user, $subscriber, $notificationType_name);  
//                }
//            } 
            
//            //on coche l'action correspondante dans la checklist
//            $em->getRepository('KsUserBundle:ChecklistAction')->checkCommentLikeShareEvolution($user->getId());
            
            $responseDatas["responseVote"]  = 1;
        } else {
            $responseDatas["responseVote"] = -1;
            $voteEvolutionMsg = $this->get('translator')->trans('Tu ne peux pas voter plus !');
            $responseDatas["errorMessage"] = $voteEvolutionMsg;
        }
        
        $evolution->numVotes     = (int)$evolutionRep->getNumVotesOnEvolution($evolution);
        $evolution->userHasVoted = $evolutionRep->haveAlreadyVoted($evolution, $user);
        
        $responseDatas["voteLink"] = $this->render('KsEvolutionBundle:Evolution:_voteLink.html.twig', array(
            'evolution'          => $evolution
        ))->getContent();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');
        
        return $response;
    }
    
    /**
     * 
     * @Route("/removeVoteOnEvolution/{evolutionId}", requirements={"evolutionId" = "\d+"}, name = "ksEvolution_removeVoteOnEvolution", options={"expose"=true} )
     * @param int $evolutionId
     */
    public function removeVoteOnEvolutionAction($evolutionId)
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $evolutionRep    = $em->getRepository('KsEvolutionBundle:Evolution');
        $votesRep       = $em->getRepository('KsEvolutionBundle:EvolutionHasVotes');
        $user           = $this->get('security.context')->getToken()->getUser();
        
        $responseDatas = array();
        
        $evolution = $evolutionRep->find($evolutionId);
        
        if (!is_object($evolution) ) {
            $impossibleEvolutionMsg = $this->get('translator')->trans('impossible-to-find-evolution-%evolutionId%', array('%evolutionId%' => $evolutionId));
            throw new AccessDeniedException($impossibleEvolutionMsg);
        }
        
        if ( $evolutionRep->haveAlreadyVoted($evolution, $user) ) {
            $evolutionHasVotes = $votesRep->find(array("evolution" => $evolutionId, "voter" => $user->getId()));
        
            if (!is_object($evolutionHasVotes) ) {
                $impossibleVoteMsg = $this->get('translator')->trans('impossible-to-find-vote-%evolutionId%', array('%evolutionId%' => $evolutionId));
                throw new AccessDeniedException($impossibleVoteMsg);
            }

            $evolutionRep->removeVoteOnEvolution($evolutionHasVotes);
            $evolution->numVotes            = (int)$evolutionRep->getNumVotesOnEvolution($evolution);
            $evolution->userHasVoted        = $evolutionRep->haveAlreadyVoted($evolution, $user);
            $responseDatas["responseVote"]  = 1;
        } else {
            $responseDatas["responseVote"]  = -1;
            $youAlreadyRetireMsg = $this->get('translator')->trans('you-already-retire-your-evolution');
            $responseDatas["errorMessage"]  = $youAlreadyRetireMsg;
        }
        
        $responseDatas["voteLink"] = $this->render('KsEvolutionBundle:Evolution:_voteLink.html.twig', array(
            'evolution' => $evolution
        ))->getContent();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
    
    /**
     * @Route("/nextEvolutions/{nbEvolutions}", requirements={"nbEvolutions" = "\d+"}, name = "ksEvolution_nextEvolutions" )
     */
    public function nextEvolutionsAction($nbEvolutions)
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $evolutionRep    = $em->getRepository('KsEvolutionBundle:Evolution');
        
        $evolutions = $evolutionRep->getLinksToBestVotedEvolutions($nbEvolutions);
        
        return $this->render('KsEvolutionBundle:Evolution:_next_evolutions.html.twig', array('evolutions' => $evolutions)
        );
    }
}