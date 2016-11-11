<?php
namespace Ks\ActivityBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundException;

class CommentController extends Controller
{
    /**
     * @Route("/publishComment/{activityId}", name = "ksActivity_publishComment", options={"expose"=true} )
     */
    public function publishCommentAction($activityId)
    {
        $request        = $this->get('request');
        $em             = $this->getDoctrine()->getEntityManager();
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        $commentRep     = $em->getRepository('KsActivityBundle:Comment');
        $user           = $this->get('security.context')->getToken()->getUser();
        $notificationService = $this->get('ks_notification.notificationService');

        $activity = $activityRep->find($activityId);
        
        if (!is_object($activity)) {
            throw new AccessDeniedException("Impossible de trouver l'activité " . $activityId .".");
        }

        $comment = new \Ks\ActivityBundle\Entity\Comment();
        $comment->setUser($user);
        $comment->setActivity($activity);
 
        $form = $this->createForm(new \Ks\ActivityBundle\Form\CommentType($activityId), $comment);
        
         // On crée le gestionnaire pour ce formulaire, avec les outils dont il a besoin
        $formHandler = new \Ks\ActivityBundle\Form\CommentHandler($form, $request, $em);

        $responseDatas = $formHandler->process();
        
        //Si le commentaire est publié, on envoi une notification au propriétaire de l'activité
        if ($responseDatas["publishResponse"] == 1) {
            
            //On modifie la date de dernière moficiation de l'activité pour que celle-ci remonte dans le fil
            $activityRep->updateLastModificationDate($activity);
            
            //Celui qui a posté le commentaire est abonné aux notifications sur l'activité
            if ($activityRep->isNotSubscribed($activity, $user)) {
                $activityRep->subscribeOnActivity($activity, $user);
            }

            //Une notification pour celui qui a posté l'activité s'il n'est pas désabonné
            if ($activity->getUser() != null) {
                if ($activity->getUser() != $user) {
                    $notificationService->sendNotification($activity, $user, $activity->getUser(), "comment");
                }
            }

            //Une notification de commentaire pour chaque abonné
            foreach ($activity->getSubscribers() as $key => $activityHasSubscribers) {
                $subscriber = $activityHasSubscribers->getSubscriber();

                //Si l'abonné n'est pas lui même et qu'il n'a pas posté l'activité
                if($subscriber != $user && $activity->getUser() != $subscriber) {
                    $notificationService->sendNotification($activity, $user, $subscriber, "comment");
                }
            }
            
            //on coche l'action correspondante dans la checklist
            $em->getRepository('KsUserBundle:ChecklistAction')->checkCommentLikeShareActivity($user->getId());
            
            //var_dump($activityRep->getCommentDatas($responseDatas['comment']->getId()));
            $responseDatas["commentHtml"] = $this->render('KsActivityBundle:Comment:_comment.html.twig', array(
                'comment' => $activityRep->getCommentDatas($responseDatas['comment']->getId())
            ))->getContent();
            
            $responseDatas["subscriptionHtml"] = $this->render('KsActivityBundle:Activity:_subscriptionLink.html.twig', array(
                'activity'   => $activityRep->getActivityDatas($activity->getId())
            ))->getContent();
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');
        
        return $response;    
    }
    
    /**
     * @Route("/getCommentForm/{activityId}", name = "ksActivity_getCommentForm", options={"expose"=true} )
     */
    public function getCommentFormAction($activityId)
    {
        //$request      = $this->get('request');
        $em             = $this->getDoctrine()->getEntityManager();
        $activityLogic  = $em->getRepository('KsActivityBundle:Activity');
        $user           = $this->get('security.context')->getToken()->getUser();
        
        $activity = $activityLogic->find($activityId);
        
        if (!is_object($activity) ) {
            throw new AccessDeniedException("Impossible de trouver l'activité " . $activityId .".");
        }
        
        $comment = new \Ks\ActivityBundle\Entity\Comment();
        $comment->setUser($user);
        $comment->setActivity($activity);
 
        $form = $this->createForm(new \Ks\ActivityBundle\Form\CommentType($activityId), $comment)->createView();
        
        return $this->render('KsActivityBundle:Comment:_commentForm.html.twig', array(
            'form'          => $form,
            'activityId'    => $activityId
        ));
    }
    
    /**
     * @Route("/notDisplayedComments", name = "ksActivity_getNotDisplayedLastComments", options={"expose"=true} )
     */
    public function getNotDisplayedCommentsAction()
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $repository     = $em->getRepository('KsActivityBundle:Comment');
        $request        = $this->get('request');
        $user           = $this->get('security.context')->getToken()->getUser();
        
        if ($request->isXmlHttpRequest()) {
            //$lastDisplayedActivityId = $request->request->get('lastDisplayedActivityId');
            $lastRefreshTime = $request->request->get('lastRefreshTime');
        }
        $comments = $repository->findNotDisplayedFriendComments($user, $lastRefreshTime);
        //$activities = $repository->findFriendActivities($user, 5, 1);
        //var_dump($comments);
        $aComments = array();
        foreach($comments as $key => $comment) {
            $aComments[$key]["activity"]["id"] = $comment->getActivity()->getId();
            $aComments[$key]["id"] = $comment->getId();
            $aComments[$key]["user"] = array(
                "id"        => $comment->getUser()->getId(),
                "username"  => $comment->getUser()->getUsername()
            );
            $aComments[$key]["comment"] = $comment->getComment();
            $aComments[$key]["commentedAt"] = $comment->getCommentedAt();
        }
        $responseDatas = array(
            'getNotDisplayedCommentsResponse' => 1,
            'comments' => $aComments,
            'lastRefreshTime' => time()
        ); 
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
}
