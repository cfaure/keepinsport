<?php
namespace Ks\TrophyBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TrophyController extends Controller
{      
    /**
     * @Route("/{userId}", name = "ks_activity_trophiesList" )
     */
    public function trophiesListAction($userId)
    {
        $em          = $this->getDoctrine()->getEntityManager();
        $userRep     = $em->getRepository('KsUserBundle:User');
        $showcaseRep = $em->getRepository('KsTrophyBundle:Showcase');
        $showcaseExposesTrophiesRep = $em->getRepository('KsTrophyBundle:ShowcaseExposesTrophies');
        $trophyRep                  = $em->getRepository('KsTrophyBundle:Trophy');
        $userRep                    = $em->getRepository('KsUserBundle:User');
        $trophyCategoryRep          = $em->getRepository('KsTrophyBundle:TrophyCategory');
        $userWinTrophiesRep         = $em->getRepository('KsTrophyBundle:UserWinTrophies');
        
        $trophyCategory     = new \Ks\TrophyBundle\Entity\TrophyCategory();
        $trophyCategoryForm = $this->createForm(new \Ks\ActivityBundle\Form\TrophyCategoryType(), $trophyCategory);   
        
        //On récupère l'utilisateur connecté
        $user = $userRep->find($userId);

        // Et pour vérifier que l'utilisateur est authentifié (et non un anonyme)
        if( ! is_object($user) )
        {
           throw $this->createNotFoundException('Impossible de trouver l\'utilisateur ' . $userId . '.');
        }
        
        $showcase = $user->getShowcase();
        $trophies = $trophyRep->findAll();
        $wonTrophies  = $userWinTrophiesRep->findByUser($user->getId());
        
        //Je ne suis pas arrivé à faire en DQL donc je fais en php
        $aTrophies = array();
        foreach($trophies as $key => $trophy) {
            $isUnlocked = false;
            $unlockedAt = null;
            foreach($wonTrophies as $wonTrophy) {
                if ( $wonTrophy->getTrophy() == $trophy ) {
                    $isUnlocked = true;
                    $unlockedAt = $wonTrophy->getUnlockedAt();
                    break;
                }
            }
            $aTrophies[$key] = array(
                "id"            => $trophy->getId(),
                "code"          => $trophy->getCode(),
                "category"      => array(
                    "id"            => $trophy->getCategory()->getId(),
                    "label"         => $trophy->getCategory()->getLabel()
                ),
                "label"         => $trophy->getLabel(),
                "pointsNumber"  => $trophy->getPointsNumber()
            );
            
            $aTrophies[$key]["isUnlocked"]          = $isUnlocked;
            $aTrophies[$key]["unlockedAt"]          = $unlockedAt;
            $aTrophies[$key]["isAlreadyInShowcase"] = $showcaseExposesTrophiesRep->isAlreadyInShowcase($showcase, $trophy);
        }
        
        
        return $this->render('KsTrophyBundle:Trophy:_trophiesDynamicList.html.twig', array(
            'trophies'              => $aTrophies,
            'user'                  => $user
        ));
        
        //$trophiesInShowcase = $showcaseRep->findAll();
        //$trophiesInShowcase = $showcaseExposesTrophiesRep->findByShowcase($user->getShowcase()->getId());
        //

        /*return $this->render('KsTrophyBundle:Trophy:_trophiesCategoryForm.html.twig', array(
            'form'                  => $trophyCategoryForm->createView(),
            'user'                  => $user
        ));*/
    }
    
    /**
     * @Route("/getTrophiesInThisCategory/{userId}_{trophyCategoryId}", name = "ksActivity_getTrophiesInThisCategory", options={"expose"=true} )
     */
    public function getTrophiesInThisCategoryAction($userId, $trophyCategoryId)
    {
        $em                         = $this->getDoctrine()->getEntityManager();        
        $trophyRep                  = $em->getRepository('KsTrophyBundle:Trophy');
        $userRep                    = $em->getRepository('KsUserBundle:User');
        $trophyCategoryRep          = $em->getRepository('KsTrophyBundle:TrophyCategory');
        $userWinTrophiesRep         = $em->getRepository('KsTrophyBundle:UserWinTrophies');
        $showcaseExposesTrophiesRep = $em->getRepository('KsTrophyBundle:ShowcaseExposesTrophies');
        
        $user = $userRep->find($userId);

        // Et pour vérifier que l'utilisateur est authentifié (et non un anonyme)
        if( ! is_object($user) )
        {
            throw new AccessDeniedException('Vous n\'êtes pas authentifié.');
        }
        
        //$trophies = $trophyRep->findLockAnUnlockTrophies($user);
        
        if ( $trophyCategoryId != -1 ) {
            $trophyCategory = $trophyCategoryRep->find($trophyCategoryId);
            
            if (!is_object($trophyCategory) ) {
                throw new AccessDeniedException("Impossible de trouver la catégorie de trophés " . $trophyCategoryId .".");
            }
            
            $trophies = $trophyRep->findByCategory($trophyCategory->getId());
            //$trophies = $trophyRep->findAll();
            /*if (!is_object($trophies) ) {
                throw new AccessDeniedException("Impossible de trouver les trophés de la catégorie " . $trophyCategoryId .".");
            }*/
        } else {
            $trophies = $trophyRep->findAll();
        }
        $wonTrophies  = $userWinTrophiesRep->findByUser($user->getId());

        /*$trophies = $trophyRep->findBy(
            array('user' => $user->getId()),
            array('pointsNumber' => 'asc')
        );*/
        
        $showcase = $user->getShowcase();
        
        if( ! is_object($showcase) )
        {
            throw new AccessDeniedException('Vous n\'avez pas de vitrine');
        }
        
        //Je ne suis pas arrivé à faire en DQL donc je fais en php
        $aTrophies = array();
        foreach($trophies as $key => $trophy) {
            $isUnlocked = false;
            $unlockedAt = null;
            foreach($wonTrophies as $wonTrophy) {
                if ( $wonTrophy->getTrophy() == $trophy ) {
                    $isUnlocked = true;
                    $unlockedAt = $wonTrophy->getUnlockedAt();
                    break;
                }
            }
            $aTrophies[$key] = array(
                "id"            => $trophy->getId(),
                "code"          => $trophy->getCode(),
                "category"      => array(
                    "id"            => $trophy->getCategory()->getId(),
                    "label"         => $trophy->getCategory()->getLabel()
                ),
                "label"         => $trophy->getLabel(),
                "pointsNumber"  => $trophy->getPointsNumber()
            );
            
            $aTrophies[$key]["isUnlocked"]          = $isUnlocked;
            $aTrophies[$key]["unlockedAt"]          = $unlockedAt;
            $aTrophies[$key]["isAlreadyInShowcase"] = $showcaseExposesTrophiesRep->isAlreadyInShowcase($showcase, $trophy);
        }
        
        return $this->render('KsTrophyBundle:Trophy:_trophiesList.html.twig', array(
            'trophies'              => $aTrophies,
            'user'                  => $user
        ));
    }
    
    /**
     * @Route("/exposeTrophyInMyShowcase/{trophyId}", requirements={"trophyId" = "\d+"}, name = "ksActivity_exposeTrophyInMyShowcase", options={"expose"=true} )
     */
    public function exposeTrophyInMyShowcaseAction($trophyId)
    {
        $em                          = $this->getDoctrine()->getEntityManager();        
        $trophyRep                   = $em->getRepository('KsTrophyBundle:Trophy');
        $showcaseRep                 = $em->getRepository('KsTrophyBundle:Showcase');
        $showcaseExposesTrophiesRep  = $em->getRepository('KsTrophyBundle:ShowcaseExposesTrophies');
        
        //On récupère l'utilisateur connecté
        $user = $this->container->get('security.context')->getToken()->getUser();

        // Et pour vérifier que l'utilisateur est authentifié (et non un anonyme)
        if( ! is_object($user) )
        {
            throw new AccessDeniedException('Vous n\'êtes pas authentifié.');
        }
       
        $trophy = $trophyRep->find($trophyId);

        if (!is_object($trophy) ) {
            throw new AccessDeniedException("Impossible de trouver le trophé " . $trophyId .".");
        }
        
        $responseDatas = array();
        
        $showcase = $user->getShowcase();
        if (! $showcaseExposesTrophiesRep->isAlreadyInShowcase($showcase, $trophy) ) {
            if( ! $showcaseExposesTrophiesRep->isFull($showcase) ) {
                $showcaseExposesTrophiesRep->exposeTrophieInShowcase($showcase, $trophy);
                $responseDatas["exposeResponse"] = 1;

                $responseDatas['html'] = $this->render('KsTrophyBundle:Trophy:_showcase.html.twig', array(
                    'mini'                  => false
                ))->getContent();
            } else {
                $responseDatas["exposeResponse"] = -1;
                $responseDatas["errorMessage"] = "La vitrine est pleine. Retirez d'abord un trophé.";
            }
        
        } else {
            $responseDatas["exposeResponse"] = -1;
            $responseDatas["errorMessage"] = "Le trophé est déjà dans la vitrine";
        }
   
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response;    
    }
    
    /**
     * @Route("/takeOfFromShowcase/{trophyId}", requirements={"trophyId" = "\d+"}, name = "ksActivity_takeOfFromShowcase", options={"expose"=true} )
     */
    public function takeOfFromShowcaseAction($trophyId)
    {
        $em                          = $this->getDoctrine()->getEntityManager();        
        $trophyRep                   = $em->getRepository('KsTrophyBundle:Trophy');
        $showcaseExposesTrophiesRep  = $em->getRepository('KsTrophyBundle:ShowcaseExposesTrophies');
        
        //On récupère l'utilisateur connecté
        $user = $this->container->get('security.context')->getToken()->getUser();

        // Et pour vérifier que l'utilisateur est authentifié (et non un anonyme)
        if( ! is_object($user) )
        {
            throw new AccessDeniedException('Vous n\'êtes pas authentifié.');
        }
        
        $responseDatas = array();
        
        $showcase = $user->getShowcase();
        
        $trophy = $trophyRep->find($trophyId);

        if (!is_object($trophy) ) {
            throw new AccessDeniedException("Impossible de trouver le trophé " . $trophyId .".");
        }
        
       
        if ( $showcaseExposesTrophiesRep->isAlreadyInShowcase($showcase, $trophy) ) {
            
            $trophyInShowcase = $showcaseExposesTrophiesRep->findOneBy(array("showcase" => $showcase->getId(), "trophy" => $trophyId));

            if (!is_object($trophyInShowcase) ) {
                throw new AccessDeniedException("Impossible de trouver le trophé " . $trophyId ." dans la vitrine.");
            }
            
            $showcaseExposesTrophiesRep->takeOfTrophyFromShowcase($trophyInShowcase);
            $responseDatas["takeOfResponse"] = 1;

            $responseDatas['html'] = $this->render('KsTrophyBundle:Trophy:_showcase.html.twig', array(
                'mini'                  => false
            ))->getContent();
        
        } else {
            $responseDatas["takeOfResponse"] = -1;
            $responseDatas["errorMessage"] = "Le trophé a déjà été retiré de la vitrine";
        }

        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response;    
    }
    
}
