<?php

namespace Ks\SearchBundle\Service;

use Symfony\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Ks\ActivityBundle\Activity;
use Ks\NotificationBundle\Service\NotificationService;

/**
 *
 * @author CeD
 */
class SearchService
{
    protected $doctrine;
    protected $notificationService;
    
    /**
     *
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine, NotificationService $notificationService)
    {
        $this->doctrine = $doctrine;
        $this->notificationService = $notificationService;
    }
    
    /**
     * 
     * @return string
     */
    public function getName()
    {
        return 'SearchService';
    }
    
     public function findResults( $params = array() ) {
        $em             = $this->doctrine->getEntityManager();
        $dbh            = $em->getConnection();
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        $userRep        = $em->getRepository('KsUserBundle:User');
        $clubRep        = $em->getRepository('KsClubBundle:Club');
        $agendaRep      = $em->getRepository('KsAgendaBundle:Agenda');
        
        $results = array();
        $text = array();
                
        if( isset( $params['term'] ) && !empty( $params['term'] )) {
            //findUsers 
            $users = $userRep->findUsers(array(
                "searchTerm" => $params['term']
            ));
            
            foreach( $users as $user ) {
                $temp = $user["firstName"] != null && $user["lastName"] != null ? $user["username"] ." (". $user["firstName"] ." ". $user["lastName"] . ")" : $user["username"];
                $results[] = array(
                    "type"  => "user",
                    "id"    => $user["id"],
                    "text"  => $temp
                    /*"img"   => $this->renderView('KsUserBundle:User:_activeServiceButton.html.twig', array(
                        'service'        => $service,
                        "user"       => $user
                    ))*/
                );
                $text[] = $temp;
            }
            
            //find Clubs
            $clubs = $clubRep->findClubs(array(
                "searchTerm" => $params['term']
            ));
            
            foreach( $clubs as $club ) {
                $results[] = array(
                    "type"  => "club",
                    "id"    => $club["id"],
                    "text"  => $club["name"],
                );
                $text[] = $club["name"];
            }
            
            //find articles
            $articles = $activityRep->findActivities(array(
                "searchTerm" => $params['term'],
                "activitiesTypes" => array("article")
            ));
            
            foreach( $articles as $article ) {
                $results[] = array(
                    "type"  => $article["activity"]["type"],
                    "id"    => $article["activity"]["id"],
                    "text"  => $article["activity"]["label"],
                );
                $text[] = $article["activity"]["label"];
            }
            
            /* FMO : mise en commentaire pour la V2 car page show event hors scope pour le moment
            //find Events
            $events = $agendaRep->findAgendaEvents(array(
                "searchTerm" => $params['term'],
                "userId" => $params["userId"]
            ));
            
            $doublon = false;
            foreach( $events as $event ) {
                //FMO : Traitement des doublons possibles article/event (exemple : article de type événement sportif)
                //Pas possible avec array_unique qui fait nimp :)
                for($i=0;$i<count($text);$i++){
                    if ($text[$i] == $event["title"]) $doublon = true;
                } 
                if ($doublon == false) {
                    $results[] = array(
                        "type"  => "event",
                        "id"    => $event["id"],
                        "text"  => $event["title"],
                        "user_id"   => $event["user_id"],
                        "club_id"   => $event["club_id"],
                    );
                }
                $text[] = $event["title"];
            }*/
        } 
        return $results;
     }
}