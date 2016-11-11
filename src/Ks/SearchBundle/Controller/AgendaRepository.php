<?php

namespace Ks\AgendaBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * AgendaRepository

 */
class AgendaRepository extends EntityRepository
{
    public function getStatementForAgendaEvents(array $params)
    {
        $dbh        = $this->_em->getConnection();
        $vars       = array();
        $sqlParts   = array(
            'select' => 'SELECT'
                .' e.id, e.name as title, e.startDate as start, e.endDate as end, e.content, e.isAllDay as allDay, e.isPublic, e.activitySession_id, '
                .' te.nom_type as type, te.color,'
                .' s.codeSport as sport_code, s.id as sport_id, s.label as sport_label, '
                .' e.user_id, e.club_id, c.name as club_name,'
                .' p.town_label, p.full_adress ',
            'from'  => 'FROM ks_event e'
                .' LEFT JOIN ks_type_event te on (te.id = e.typeEvent_id)'
                .' LEFT JOIN ks_agenda_has_events ahe on (ahe.event_id = e.id)'
                .' LEFT JOIN ks_agenda a on (a.id = ahe.agenda_id)'
                .' LEFT JOIN ks_user u on (a.id = u.agenda_id)'
                .' LEFT JOIN ks_club c on (c.id = e.club_id)'
                .' LEFT JOIN ks_place p on (p.id = e.place_id)'
                .' LEFT JOIN ks_sport s on (s.id = e.sport_id)',
            'where' => 'WHERE 1',
            'group' => 'GROUP BY e.id',
            'order' => '',
            'limit' => ''
        );
        
       if (isset($params['eventId']) && $params['eventId'] > 0) { 
            $sqlParts['where'] .= ' AND e.id = :eventId';
            $vars['eventId'] = $params['eventId'];
        }
        
        //Rien n'est coché dans le from sur l'agenda d'un utilisateur, on affiche tout
        if( isset($params['userId']) && $params['userId'] != '' && empty( $params['eventsFrom'] ) ) {
            $params['eventsFrom'][] = "me";
            $params['eventsFrom'][] = "my_clubs";
            $params['eventsFrom'][] = "public";
        }
        
        if( isset($params['userId']) && $params['userId'] != '' && empty( $params['eventsTypes'] ) ) {
            $params['eventsTypes'][] = "competitions";
            $params['eventsTypes'][] = "trainings";
            $params['eventsTypes'][] = "meals";
            $params['eventsTypes'][] = "others";
        }
        
        //Agenda utilisateurs
        if( isset($params['userId']) && $params['userId'] != '' ) {
            $sqlParts['where'] .= ' AND ( 0';
                //Les événements de l'utilisateur
                if( isset($params['userId']) && $params['userId'] != '' && in_array("me", $params['eventsFrom']) ) {
                    $sqlParts['where'] .= ' OR e.user_id = :userId';
                    $vars['userId']   = $params['userId'];
                }
                
                //Les événements des clubs de l'utilisateur
                if( isset($params['clubIds']) && !empty( $params['clubIds'] ) && in_array("my_clubs", $params['eventsFrom']) ) {
                    $sqlParts['where'] .= ' OR (e.club_id in ('.implode(',', $params['clubIds']).') AND e.user_id IS NULL )';
                }
                
                //Tous les autres événements publics
                 if( in_array("public", $params['eventsFrom'] ) ) {
                    $sqlParts['where'] .= ' OR e.isPublic = :isPublic ';
                    $vars['isPublic']   = (int)true;
                }

            $sqlParts['where'] .= ')';
            
        } 
        //Agenda clubs
        elseif (isset($params['clubIds']) && $params['clubIds'] != '') {
            $sqlParts['where'] .= ' AND e.club_id in ('.implode(',', $params['clubIds']).') ';
        }
        
        //Ne sert plus
        if (isset($params['agendaId']) && $params['agendaId'] != '') {
            $sqlParts['where'] .= ' AND a.id = :agendaId';
            $vars['agendaId']   = $params['agendaId'];
        }
        
        if (isset($params['startOn']) && $params['startOn'] != '') {
            $sqlParts['where'] .= ' AND SUBSTRING(e.startDate, 1, 10) >= :startOn';
            $vars['startOn']   = $params['startOn'];
        }
        if (isset($params['endOn']) && $params['endOn'] != '') {
            $sqlParts['where'] .= ' AND SUBSTRING(e.endDate, 1, 10) <= :endOn';
            $vars['endOn']   = $params['endOn'];
        }
        
        //Gestion des filtres
        if (isset($params['eventsTypes']) && !empty($params['eventsTypes']) ) {
           
            $eventTypes = array();
            
            if( in_array("competitions", $params['eventsTypes']) ) {
                $eventTypes[] = "event_competition";
            }
            if( in_array("trainings", $params['eventsTypes']) ) {
                $eventTypes[] = "event_training";
            }
            if( in_array("meals", $params['eventsTypes']) ) {
                $eventTypes[] = "event_meal";
            }
            
            if( count( $eventTypes ) > 0 ) {
                $sqlParts['where'] .= ' AND (';
                $sqlParts['where'] .= ' te.nom_type = \''.implode('\' OR te.nom_type =\'', $eventTypes).'\'';
                
                if( in_array("others", $params['eventsTypes']) ) {
                    $sqlParts['where'] .= ' OR te.nom_type IS NULL ';
                }
                
                $sqlParts['where'] .= ' ) ';
            } else {
                if( in_array("others", $params['eventsTypes']) ) {
                    $sqlParts['where'] .= ' AND te.nom_type IS NULL ';
                }
            }
        } else {
            //$sqlParts['where'] .= ' AND te.nom_type IS NOT NULL AND te.nom_type = \'test\'';
        }
        
        if (isset($params['limit']) && $params['limit'] != '') {
            $sqlParts['limit'] .= ' LIMIT ' . $params['limit'];
        }
        
        if (isset($params['order']) && is_array($params['order'])) {
            $sqlParts['order'] .= ' ORDER BY ' . key($params['order']) . ' ' .$params['order'][key($params['order'])];
        } else {
            //tri par date de débuit décroissant
            $sqlParts['order'] .= 'ORDER BY e.startDate DESC';
        }
        
        
        //var_dump(implode(' ', $sqlParts));
        
        return $dbh->executeQuery(implode(' ', $sqlParts), $vars);
    }
    
    /**
     *
     * @param array $params
     * 
     * @return array tableau de données sur les events
     */
    public function findAgendaEvents(array $params, $translator = null)
    {
        $userRep    = $this->_em->getRepository('KsUserBundle:User');
        
        $events = array();
        if (isset($params['clubId'])) {
            $params['clubIds'] = array($params['clubId']);
        }
        elseif (isset($params['userId']) && $params['userId'] != '') {
            //var_dump($params['userId']);
            $clubIds    = $userRep->getClubIds($params['userId']);
            $params['clubIds'] = $clubIds;
        }
        
        $stmt                       = $this->getStatementForAgendaEvents($params);
        $eventParticipationsStmt    = $this->getPreparedStatementForEventParticipations();
        
        
        while ($event = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            //On transforme les variables en booléen
            $event["allDay"] = $event["allDay"] == "1" ? true : false;
            $event["isPublic"] = $event["isPublic"] == "1" ? true : false;
            
            if ( $event["club_id"] != null ) {
                $event["color"] = "#F8C70B";
            }
            else if ( $event["user_id"] != null ) {
                $event["color"] = "#1787c7";
            } else {
                $event["color"] = "#DB2033";
            }
            
            //On récupère le nom du sport traduit
            if ( $event["sport_code"] != null && $translator != null ) {
                $event["sport_label"] = $translator->trans($event["sport_code"]);
            } 
            
            if (isset($params['extended']) && $params['extended'] ) {
                $eventParticipationsStmt->execute(array('eventId' => $event['id']));
                $events[] = array_merge(
                    array('event' => $event),
                    array('participations' => $eventParticipationsStmt->fetchAll(\PDO::FETCH_ASSOC))
                );
            } else {
                $events[] = $event;
            }
            
            
        }
        
        if (isset($params['eventId']) && count($events) == 1) {
            return $events[0];
        } else {
            return $events;
        }
    }
    
     protected final function getPreparedStatementForEventParticipations()
    {
        $dbh        = $this->_em->getConnection();
        $sql = 'select u.id as user_id, u.username as user_username, '
            .' ud.image_name as user_imageName,'
            .' ll.starNumber as ll_starNumber,'
            .' ll_category.label as ll_categoryLabel'
            .' FROM ks_user_participates_event upe'
            .' INNER join ks_user u on (u.id = upe.user_id)'
            .' LEFT JOIN ks_user_detail ud on (u.userDetail_id = ud.id)'
            .' LEFT JOIN ks_league_level ll on (ll.id = u.leagueLevel_id)'
            .' INNER JOIN ks_league_category ll_category on (ll.category_id = ll_category.id)'
            .' WHERE upe.event_id = :eventId';
        
        return $dbh->prepare($sql);
    }
    
    public function addEventToAgenda( $agenda, $event) {
        $agendaHasEvent = new \Ks\AgendaBundle\Entity\AgendaHasEvents($agenda, $event);
        $this->_em->persist($agendaHasEvent);
        $this->_em->flush();
    }
    
    public function moveOrResizeEvent( $event, $dayDelta, $minuteDelta, $isMove) {
        $startDateTime = $event->getStartDate();
        $endDateTime = $event->getEndDate();

        if($dayDelta < 0){
            $dayDelta = substr($dayDelta, 1, strlen($dayDelta));
            $interval = 'P'.$dayDelta.'D';
            $i = new \DateInterval( $interval );
            
            //On ne touche à la date de début que si c'est un déplacement et non un resize
            if ( $isMove ) date_sub($startDateTime, $i);
            
            date_sub($endDateTime, $i);
        }else{
            $interval = 'P'.$dayDelta.'D';
            $i = new \DateInterval( $interval );
            if ( $isMove ) date_add($startDateTime, $i);
            
            date_add($endDateTime, $i);
        }

        if($minuteDelta < 0){
            $minuteDelta = substr($minuteDelta, 1, strlen($minuteDelta));
            $interval = 'PT'.$minuteDelta.'M';
            $i = new \DateInterval( $interval );
            
            //On ne touche à la date de début que si c'est un déplacement et non un resize
            if ( $isMove ) date_sub($startDateTime, $i);
            
            date_sub($endDateTime, $i);
        } else {
            $interval = 'PT'.$minuteDelta.'M';
            $i = new \DateInterval( $interval );
            
            //On ne touche à la date de début que si c'est un déplacement et non un resize
            if ( $isMove ) date_add($startDateTime, $i);
            
            date_add($endDateTime, $i);
        }

        //On ne touche à la date de début que si c'est un déplacement et non un resize
        if ( $isMove ) $event->setStartDate(new \DateTime($startDateTime->format("Y-m-d H:i:s")));
        
        $event->setEndDate(new \DateTime($endDateTime->format("Y-m-d H:i:s")));
        $event->setLastModificationDate(new \DateTime("Now"));
        
        
        $this->_em->persist($event);
        $this->_em->flush();
    }
    
    public function getFranceGeo() {
        $dbh    = $this->_em->getConnection();
        
        $aGeo = array();
        
        $stmtRegion   = $dbh->executeQuery(
            'select id, code, label'
            .' from ks_region',
            array()
        );
        
        $aGeo["regions"] = $stmtRegion->fetchAll(\PDO::FETCH_ASSOC);
        
        $stmtCounty   = $dbh->executeQuery(
            'select id, code, label'
            .' from ks_county',
            array()
        );
        
        $aGeo["counties"] = $stmtCounty->fetchAll(\PDO::FETCH_ASSOC);
        
        $stmtTown   = $dbh->executeQuery(
            'select id, label'
            .' from ks_town',
            array()
        );
        
        $aGeo["towns"] = $stmtTown->fetchAll(\PDO::FETCH_ASSOC);
        
        return $aGeo;
        
    }
    
}
