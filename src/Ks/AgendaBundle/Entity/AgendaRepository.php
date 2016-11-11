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
                .' e.id, e.name as title, e.startDate as start, DATE_FORMAT(e.startDate, "%u") as weekNumber, e.endDate as end, e.content, e.isAllDay as allDay, e.isPublic, e.isWarning, e.activitySession_id, e.typeEvent_id, e.difficulty_id, '
                .' te.nom_type as type, te.color,'
                .' ac.type as activity_type, ac.intensity_id as intensity_id, ac.stateOfHealth_id as stateOfHealth_id, ac.achievement, '
                .' ac.description, ac.points, ar.code as result, '
                .' IFNULL(ac.distance, \'-\') as distance, IFNULL(ac.elevationGain, \'-\') as elevationGain, IFNULL(ac.elevationLost, \'-\') as elevationLost, IFNULL(LEFT(ac.duration, 5), \'-\') as duration,'
                .' sEvent.codeSport as sport_code, sEvent.id as sport_id, sEvent.label as sport_label, ac.issuedAt as activitySession_issuedAt, sActivity.id as activitySession_sport_id, sActivity.codeSport as activitySession_sport_code, '
                .' e.user_id, e.club_id, c.name as club_name, c.avatar, ud.HRMax, ud.HRRest, '
                .' p.town_label, p.full_adress, p.country_code, '
                .' e.maxParticipations as maxParticipations, '
                .' e.coachingPlan_id, e.coachingCategory_id, e.coachingSession_id, cp.name as plan, cp.father_id, ct.name as sessionTitle, ct.detail as coachingSessionDetail, '
                .' cc.name as category, '
                .' ct.distanceMin as coachingDistanceMin, ct.distanceMax as coachingDistanceMax, ct.durationMin as coachingDurationMin, ct.durationMax as coachingDurationMax, '
                .' ct.elevationGainMin as coachingElevationGainMin, ct.elevationGainMax as coachingElevationGainMax, ct.elevationLostMin as coachingElevationLostMin, ct.elevationLostMin as coachingElevationLostMax, '
                .' ct.speedAverageMin as coachingSpeedAverageMin, ct.speedAverageMax as coachingSpeedAverageMax, ct.powMin as coachingPowMin, ct.powMax as coachingPowMax, '
                .' ct.hrCoeffMin as coachingHrCoeffMin, ct.hrCoeffMax as coachingHrCoeffMax, ct.hrtype as coachingHrType, ct.VMACoeff as coachingVMACoeff, ct.intervalDistance as coachingIntervalDistance, ct.intervalDuration as coachingIntervalDuration, '
                .' e.competition as eventCompetition, acc.label as eventCompetitionLabel, e.distanceMin as eventDistanceMin, e.distanceMax as eventDistanceMax, e.durationMin as eventDurationMin, e.durationMax as eventDurationMax, '
                .' e.elevationGainMin as eventElevationGainMin, e.elevationGainMax as eventElevationGainMax, e.elevationLostMin as eventElevationLostMin, e.elevationLostMax as eventElevationLostMax, '
                .' e.speedAverageMin as eventSpeedAverageMin, e.speedAverageMax as eventSpeedAverageMax, e.powMin as eventPowMin, e.powMax as eventPowMax, '
                .' e.hrCoeffMin as eventHrCoeffMin, e.hrCoeffMax as eventHrCoeffMax, e.hrtype as eventHrType, e.VMACoeff as eventVMACoeff, e.intervalDistance as eventIntervalDistance, e.intervalDuration as eventIntervalDuration, '
                .' (select count(1) from ks_event_has_users ehu where ehu.event_id = e.id) as usersParticipations ',
            'from'  => 'FROM ks_event e'
                .' LEFT JOIN ks_type_event te on (te.id = e.typeEvent_id)'
                //.' LEFT JOIN ks_agenda_has_events ahe on (ahe.event_id = e.id)'
                //.' LEFT JOIN ks_agenda a on (a.id = ahe.agenda_id)'
                //.' LEFT JOIN ks_user u on (a.id = u.agenda_id)'
                .' LEFT JOIN ks_event_has_users ehu on (ehu.event_id = e.id)'
                .' LEFT JOIN ks_club c on (c.id = e.club_id)'
                .' LEFT JOIN ks_place p on (p.id = e.place_id)'
                .' LEFT JOIN ks_coaching_plan cp on (cp.id = e.coachingPlan_id)'
                .' LEFT JOIN ks_coaching_plan_type cpt on (cpt.id = cp.coachingPlanType_id)'
                .' LEFT JOIN ks_coaching_session ct on (ct.id = e.coachingSession_id)'
                .' LEFT JOIN ks_coaching_category cc on (cc.id = e.coachingCategory_id)'
                .' LEFT JOIN ks_activity ac on (ac.id = e.activitySession_id)'
                .' LEFT JOIN ks_activity acc on (acc.id = e.competition)'
                .' LEFT JOIN ks_user u on (u.id = ac.user_id)'
                .' LEFT JOIN ks_user_detail ud on (ud.id = u.userDetail_id)'
                .' LEFT JOIN ks_activity_result ar on (ar.id = ac.result_id)'
                .' LEFT JOIN ks_activity_earns_points aep on (aep.activitySession_id = ac.id)'
                .' LEFT JOIN ks_sport sEvent on (sEvent.id = e.sport_id)'
                .' LEFT JOIN ks_sport sActivity on (sActivity.id = ac.sport_id)',
            'where' => 'WHERE 1',
            'group' => 'GROUP BY e.id',
            'order' => '',
            'limit' => ''
        );
        
        if( isset( $params['searchTerm'] ) && $params['searchTerm'] != '' ) {
            $sqlParts["where"] .= ' AND e.name LIKE :searchTerm';
                $vars['searchTerm'] = "%" . $params['searchTerm'] . "%";
        }  
        
       if (isset($params['eventId']) && $params['eventId'] > 0) { 
            $sqlParts['where'] .= ' AND e.id = :eventId';
            $vars['eventId'] = $params['eventId'];
        }
        
        //Rien n'est coché dans le from sur l'agenda d'un utilisateur, on affiche tout
        if( isset($params['userId']) && $params['userId'] != '' && empty( $params['eventsFrom'] ) ) {
            $params['eventsFrom'][] = "me";
            $params['eventsFrom'][] = "my_clubs";
            //$params['eventsFrom'][] = "public";
        }
        
        if( isset($params['userId']) && $params['userId'] != '' && empty( $params['eventsTypes'] ) ) {
            //$params['eventsTypes'][] = "competitions";
            $params['eventsTypes'][] = "trainings";
            $params['eventsTypes'][] = "meals";
            $params['eventsTypes'][] = "others";
            $params['eventsTypes'][] = "goals";
            $params['eventsTypes'][] = "coaching";
            $params['eventsTypes'][] = "google";
            $params['eventsTypes'][] = "misc";
        }
        
        if (isset($params['competitionsOnly']) && $params['competitionsOnly']) {
            //Cas de l'agenda des competitions
            unset($params['eventsTypes']);
            $params['eventsTypes'][] = "competitions";
        }
        
        //Pour afficher la flatView d'un plan
        if (isset($params['planId']) && !empty($params['planId'])) {
            if (isset($params['getUserSessionForFlatView']) && $params['getUserSessionForFlatView'] ) {
                $sqlParts['where'] .= ' AND (e.coachingPlan_id = :planId OR (e.user_id = :userIdForFlatView AND (cpt.code IS NULL OR cpt.code IS NOT NULL AND cpt.code NOT IN ("user", "shared"))))';
                $vars['planId']             = $params['planId'];
                $vars['userIdForFlatView']  = $params['userIdForFlatView'];
            }
            else {
                $sqlParts['where'] .= ' AND e.coachingPlan_id = :planId ';
                $vars['planId'] = $params['planId'];
            }
        }
        
        if (isset($params['fromSpecificDay']) && !empty($params['fromSpecificDay']) ) {
            $sqlParts['where'] .= ' AND DATE_FORMAT(e.startDate, "%w") = :fromSpecificDay ';
            $vars['fromSpecificDay'] = $params['fromSpecificDay'];
        }
        
        if (isset($params['notLinkedToActivity']) && $params['notLinkedToActivity'] ) {
            $sqlParts['where'] .= ' AND e.activitySession_id is null ';
        }
        
        // Agenda utilisateurs
        if (isset($params['getUserSessionForFlatView']) && !$params['getUserSessionForFlatView']) {
            //Si un coach affiche son tableau de bord userId is set donc ne doit pas rentrer dans le cas ci-dessous des sportifs "normaux"
        }
        else {
            if ( isset($params['userId']) && $params['userId'] != '') {
                //Gestion du filtre Plans d'entrainement
                if (isset($params['eventsCoachingPlans']) && count($params['eventsCoachingPlans']) > 0) {
                    $sqlParts['where'] .= ' AND cp.id in (\''.implode("','", $params['eventsCoachingPlans']).'\')';
                }
                else {
                    $sqlParts['where'] .= ' AND ( 0';
                    //Les événements de l'utilisateur
                    if( isset($params['userId']) && $params['userId'] != '' && in_array("me", $params['eventsFrom']) ) {
                        $sqlParts['where'] .= ' OR e.user_id = :userId';
                        $vars['userId']   = $params['userId'];
                    }

                    //Les événements des clubs de l'utilisateur
                    if( isset($params['clubIds']) && !empty( $params['clubIds'] ) && in_array("my_clubs", $params['eventsFrom']) ) {
                        $sqlParts['where'] .= ' OR (e.club_id in ('.implode(',', $params['clubIds']).') AND e.user_id IS NULL AND e.coachingPlan_id IS NULL AND e.coachingSession_id IS NULL)';
                    }

                    //Pour les événements de type plan d'entrainement on ne doit afficher QUE ceux liés à l'utilisateur
                    $sqlParts['where'] .= ' OR (e.coachingPlan_id IS NOT NULL and exists (select 1 from ks_event_has_users where event_id = e.id and user_id = :userId))';
                    $vars['userId']   = $params['userId'];

                    //Tous les autres événements publics
                     if( in_array("public", $params['eventsFrom'] ) ) {
                        $sqlParts['where'] .= ' OR e.isPublic = :isPublic ';
                        $vars['isPublic']   = (int)true;
                    }
                    
                    //Pour le cas des event liés aux compétitions user_id est à NULL
                    if (isset($params['competitionsOnly']) && $params['competitionsOnly'] && isset($params['userId']) && $params['userId'] == 1 ) {
                        $sqlParts['where'] .= ' OR e.user_id IS NULL ';
                    }
                    
                    $sqlParts['where'] .= ')';
                }
            } 
            //Agenda clubs
            elseif (isset($params['clubIds']) && $params['clubIds'] != '') {
                $sqlParts['where'] .= ' AND (e.club_id in ('.implode(',', $params['clubIds']).') ';
                if (isset($params['isManager'])) {
                    if (!$params['isManager']) {
                        //Si l'utilisateur n'est pas un manager on n'affiche pas les plans d'entrainements
                        $sqlParts['where'] .= ' AND e.coachingPlan_id IS NULL';
                        $sqlParts['where'] .= ' AND e.isPublic = 1 ';
                    }
                    elseif (isset($params['eventsUsers']) && $params['eventsUsers'] != '') {
                        //Si l'utilisateur est manager on affiche aussi les entrainements hors plan hors club du user sélectionné
                        $sqlParts['where'] .= ' OR e.user_id in (\''.implode("','", $params['eventsUsers']).'\')';
                    }
                }
                $sqlParts['where'] .= ' )';
            }
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
        
        if (isset($params['planId']) && !empty($params['planId']) ) {
            //Pour ne pas afficher les activités postées avant le suivi d'un plan 
            $sqlParts['where'] .= ' AND SUBSTRING(e.startDate, 1, 10) >= (select IFNULL(SUBSTRING(min(e2.startDate), 1, 10), "1970-01-01") from ks_event e2 where e2.coachingPlan_id = :planId)';
            $vars['planId'] = $params['planId'];
        }
        
        if (isset($params['endOn']) && $params['endOn'] != '') {
            $sqlParts['where'] .= ' AND SUBSTRING(e.endDate, 1, 10) <= :endOn';
            $vars['endOn']   = $params['endOn'];
        }
        
        //Gestion du filtre Type
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
            if( in_array("coaching", $params['eventsTypes']) ) {
                $eventTypes[] = "event_coaching";
            }
            if( in_array("goals", $params['eventsTypes']) ) {
                $eventTypes[] = "event_goal";
            }
            if( in_array("google", $params['eventsTypes']) ) {
                $eventTypes[] = "event_google";
            }
            if( in_array("misc", $params['eventsTypes']) ) {
                $eventTypes[] = "event_misc";
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
        }
        //Gestion du filtre Regions
        if (isset($params['eventsRegions']) && !empty($params['eventsRegions']) ) {
            
            if( count( $params['eventsRegions'] ) >= 0 ) {
                if (!in_array("UNKNOWN", $params['eventsRegions'])) {//var_dump(' p.country_code = \''.implode('\' OR p.country_code =\'', $params['eventsRegions']).'\'');
                    $sqlParts['where'] .= ' AND (';
                    $sqlParts['where'] .= ' p.country_code = \''.implode('\' OR p.country_code =\'', $params['eventsRegions']).'\'';
                    $sqlParts['where'] .= ' ) ';
                }
                else {
                    $sqlParts['where'] .= ' AND (';
                    $sqlParts['where'] .= ' p.country_code is null';
                    $sqlParts['where'] .= ' ) ';
                }
            }
        }
        //Gestion du filtre Distances
        if (isset($params['eventsDistances']) && !empty($params['eventsDistances']) ) {
            if( count( $params['eventsDistances'] ) == 2 ) {
                //var_dump(' AND ac.distance >= ' . $params['eventsDistances'][0] . ' AND ac.distance <= ' . $params['eventsDistances'][1]);exit;
                $sqlParts['where'] .= ' AND e.distance >= ' . $params['eventsDistances'][0] . ' AND e.distance <= ' . $params['eventsDistances'][1];
            }
        }
        //Gestion du filtre Sports
        if (isset($params['eventsSports']) && count($params['eventsSports']) > 0) {
            if (isset($params['my_sports']) && $params['my_sports']) {
                // filtrage sur MES sports (filtre de la page agenda)
                $mySportsIds = $this->_em->getRepository('KsUserBundle:User')->getMySportsIds($params['userId']);
                if (count($mySportsIds) == 0) {
                    $mySportsIds = array(0);
                }
                $params['my_sports'] = $mySportsIds;
                $sqlParts['where'] .= ' AND e.sport_id in (\''.implode("','", $params['my_sports']).'\', \''.implode("','", $params['eventsSports']).'\')';
            }
            else $sqlParts['where'] .= ' AND e.sport_id in (\''.implode("','", $params['eventsSports']).'\')';
        }
        
        //Gestion du filtre Users (pour la partie Club)
        if (isset($params['eventsUsers'])) {
            if (count($params['eventsUsers']) > 0) {
                $sqlParts['where'] .= ' AND (ehu.user_id in (\''.implode("','", $params['eventsUsers']).'\')';
                $sqlParts['where'] .= ' OR e.user_id in (\''.implode("','", $params['eventsUsers']).'\')';
                $sqlParts['where'] .= ' )';
                $sqlParts['where'] .= ' AND (cpt.code IS NULL OR cpt.code IS NOT NULL AND cpt.code NOT IN ("user", "shared")) '; // FMO : pour ne pas voir les event de type plan premium d'un coach
            }
            else {
                //Gestion du filtre Plans d'entrainement
                if (isset($params['eventsCoachingPlans'])) {
                    if (count($params['eventsCoachingPlans']) > 0) {
                        $sqlParts['where'] .= ' AND cp.id in (\''.implode("','", $params['eventsCoachingPlans']).'\')';
                    }
                    else {
                        $sqlParts['where'] .= ' AND (te.nom_type != \'event_coaching\' OR te.nom_type IS NULL) ';
                    }
                }
            }
        }
        
        //Gestion du filtre Disponibilité
        if (isset($params['eventsAvailability']) && !empty($params['eventsAvailability']) ) {
            if ($params['eventsAvailability'] == 'full') {
                //Uniquement les events dont le max de participants est atteint
                $sqlParts['where'] .= ' AND e.maxParticipations <= (select count(1) from ks_event_has_users ehu where ehu.event_id = e.id) ';
            }
            if ($params['eventsAvailability'] == 'available') {
                //Uniquement les events dont le max de participants n'est pas atteint, il reste des places possibles
                $sqlParts['where'] .= ' AND (e.maxParticipations is null OR e.maxParticipations > (select count(1) from ks_event_has_users ehu where ehu.event_id = e.id)) ';
            }
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
        
        
        //var_dump(implode(' ', $sqlParts));exit;
        
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
        
        $stmt                           = $this->getStatementForAgendaEvents($params);
        $eventParticipationsStmt        = $this->getPreparedStatementForEventParticipations(); //FMO : plus utilisé, table ks_user_participate_event obsolète !
        
        $eventUsersStmt                 = $this->getPreparedStatementForEventUsers(); // FMO : nouvelle table ks_event_has_users
        $eventCoachingIsNewForUserStmt  = $this->getPreparedStatementForEventCoachingIsNewForUser();
        
        //var_dump($stmt);exit;
        
        while ($event = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            //var_dump($event["id"]);
            //On transforme les variables en booléen
            $event["allDay"] = $event["allDay"] == "1" ? true : false;
            $event["isPublic"] = $event["isPublic"] == "1" ? true : false;
            /* Ancienne gestion des codes couleurs sur fullcalendar, géré maintenant coté client
            if ( $event["club_id"] != null && $event["type"] != 'event_google') {
                if ($event["maxParticipations"] != null && $event["usersParticipations"] >= $event["maxParticipations"]) {
                    $event["color"] = "rgb(242, 242, 242)"; //Couleur jaune, l'activité est "complète : F8C70B"
                    $event["borderColor"] = "#caa206";
                    $event["textColor"] = "#FFFFFF";//caa206 jaune foncé
                }
                if ($event["coachingPlan_id"] != null) {
                    $event["color"] = "rgb(242, 242, 242)"; //Fond blanc
                    
                    if ($event["difficulty_id"] != null) {
                        if ($event["difficulty_id"] == 1) $event["borderColor"] = "#028000";
                        if ($event["difficulty_id"] == 2) $event["borderColor"] = "#fda601";
                        if ($event["difficulty_id"] == 3) $event["borderColor"] = "#fd0002";
                    }
                    else $event["borderColor"] = "#caa206";
                    
                    $event["textColor"] = "#caa206";
                    
                    //Test pour savoir si le type de la séance planifiée est nouvelle ou non pour le sportif
                    if ($event["usersParticipations"] == 1) {
                        if ($event["activitySession_id"] == null) {
                            $eventUsersStmt->execute(array('eventId' => $event['id']));
                            $user = $eventUsersStmt->fetchAll(\PDO::FETCH_ASSOC);
                            //var_dump($user);

                            $eventCoachingIsNewForUserStmt->execute(array(
                                'clubId'            => $event['club_id'],
                                'userId'            => $user[0]['user_id'],
                                'coachingSessionId'   => $event['coachingSession_id'],
                                'startDate'         => $event['start'],
                                    ));
                            $result = $eventCoachingIsNewForUserStmt->fetchAll(\PDO::FETCH_ASSOC);
                            if (isset($result[0][1]) && $result[0][1] != null) $event["isNew"] = 0;
                            else $event["isNew"] = 1;
                        }
                        else $event["isNew"] = 0; //Si le sportif a lié sa séance à une de ses activités ce n'est plus "new"
                    }
                }
                else {
                    $event["color"] = "rgb(242, 242, 242)";
                    $event["borderColor"] = "#caa206";
                    $event["textColor"] = "#caa206";
                }
            }
            else if ( $event["user_id"] != null && $event["type"] != 'event_google') {
                $event["color"] = "rgb(242, 242, 242)"; //Fond couleur gris clair
                $event["textColor"] = "#1787c7";

                if ($event["difficulty_id"] != null) {
                    if ($event["difficulty_id"] == 1) $event["borderColor"] = "#028000";
                    if ($event["difficulty_id"] == 2) $event["borderColor"] = "#fda601";
                    if ($event["difficulty_id"] == 3) $event["borderColor"] = "#fd0002";
                }
                else $event["borderColor"] = "#1787c7";
            }
            
//            if ( $event["type"] == 'event_competition' ) {
//                $event["color"] = "#FFFFFF"; //Couleur blanc pour le fond
//                $event["borderColor"] = "#DB2033"; 
//                $event["textColor"] = "#DB2033"; // Couleur rouge "wikisport" pour le contour et le texte
//            }
            
            if ( $event["type"] == 'event_google' ) {
                $event["color"] = "rgb(242, 242, 242)"; //Couleur blanc pour le fond
                $event["borderColor"] = "#000000"; 
                $event["textColor"] = "#000000";
            }
            */
            
            //On récupère le nom du sport traduit
            if ( $event["sport_code"] != null && $translator != null ) {
                $event["sport_label"] = $translator->trans($event["sport_code"]);
            }
            
            if ( $event["activitySession_sport_code"] != null && $translator != null ) {
                $event["activitySession_sport_label"] = $translator->trans($event["activitySession_sport_code"]);
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
    
    protected final function getPreparedStatementForEventUsers()
    {
        //Recherche l'utilisateur lié à l'évent de type event_coaching
        $dbh        = $this->_em->getConnection();
        $sql = 'select ehu.user_id '
            .' FROM ks_event_has_users ehu'
            .' WHERE ehu.event_id = :eventId';
        
        return $dbh->prepare($sql);
    }
    
    protected final function getPreparedStatementForEventCoachingIsNewForUser()
    {
        //Recherche d'un event de type coaching avec le même type de séance avant la date de l'event ciblé
        $dbh        = $this->_em->getConnection();
        $sql = 'SELECT 1 '
            .' FROM ks_event e, ks_type_event te, ks_event_has_users ehu'
            .' WHERE e.club_id = :clubId'
            .' AND   e.typeEvent_id = te.id' 
            .' AND   te.nom_type = \'event_coaching\''
            .' AND   ehu.event_id = e.id'
            .' AND   ehu.user_id = :userId'
            .' AND   e.startDate < :startDate'
            .' AND   e.coachingSession_id = :coachingSessionId';
        
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
    
    public function duplicateEvent( $event, $dayDelta, $minuteDelta, $isMove, $newUserId=null, $coachingPlanId=null) {
        $userRep            = $this->_em->getRepository('KsUserBundle:User');
        $coachingPlanRep    = $this->_em->getRepository('KsCoachingBundle:CoachingPlan');
        
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
            $interval = 'P'.(int)$dayDelta.'D';
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
        //if ( $isMove ) $event->setStartDate(new \DateTime($startDateTime->format("Y-m-d H:i:s")));
        
        //Création d'un nouvel event basé sur l'ancien et les nouvelles dates calculées
        $newEvent = new \Ks\EventBundle\Entity\Event();
        $newEvent->setCoachingCategory($event->getCoachingCategory());
        $newEvent->setcoachingSession($event->getcoachingSession());
        $newEvent->setCompetition($event->getCompetition());
        $newEvent->setContent($event->getContent());
        $newEvent->setDifficulty($event->getDifficulty());
        
        $newEvent->setHrCoeffMin($event->getHrCoeffMin());
        $newEvent->setHrCoeffMax($event->gethrCoeffMax());
        $newEvent->setHrType($event->getHrType());
        $newEvent->setIntervalDuration($event->getIntervalDuration());
        $newEvent->setIntervalDistance($event->getIntervalDistance());
        $newEvent->setVMACoeff($event->getVMACoeff());
        $newEvent->setDurationMin($event->getDurationMin());
        $newEvent->setDurationMax($event->getDurationMax());
        $newEvent->setDistanceMin($event->getDistanceMin());
        $newEvent->setDistanceMax($event->getDistanceMax());
        $newEvent->setElevationGainMin($event->getElevationGainMin());
        $newEvent->setElevationGainMax($event->getElevationGainMax());
        $newEvent->setElevationLostMin($event->getElevationLostMin());
        $newEvent->setElevationLostMax($event->getElevationLostMax());
        $newEvent->setSpeedAverageMin($event->getSpeedAverageMin());
        $newEvent->setSpeedAverageMax($event->getSpeedAverageMax());
        $newEvent->setPowMin($event->getPowMin());
        $newEvent->setPowMax($event->getPowMax());
        
        $newEvent->setSport($event->getSport());
        $newEvent->setTypeEvent($event->getTypeEvent());
        $newEvent->setName($event->getName());
        $newEvent->setIsAllDay($event->getIsAllDay());
        $newEvent->setCreationDate(new \DateTime("Now"));
        $newEvent->setStartDate(new \DateTime($startDateTime->format("Y-m-d H:i:s")));
        $newEvent->setEndDate(new \DateTime($endDateTime->format("Y-m-d H:i:s")));
        $newEvent->setLastModificationDate(new \DateTime("Now"));
        
        if ($event->getCoachingPlan()->getCoachingPlanType()->getCode() == "shared") {
            //Cas de l'import d'un plan partagé dans l'agenda d'un user
            $newEvent->setUser($userRep->find($newUserId));
            $newEvent->setCoachingPlan($coachingPlanRep->find($coachingPlanId));
        }
        else if ($event->getCoachingPlan()->getCoachingPlanType()->getCode() == "user") {
            $newEvent->setUser($event->getUser());    
            $newEvent->setCoachingPlan($event->getCoachingPlan());
        }
        else if ($event->getCoachingPlan()->getCoachingPlanType()->getCode() == "coach") {
            $newEvent->setClub($event->getClub());
            $newEvent->setCoachingPlan($event->getCoachingPlan());
        }
        else if ($event->getCoachingPlan()->getCoachingPlanType()->getCode() == "club") {
            $newEvent->setClub($event->getClub());
            if (!is_null($newUserId)) {
                //Cas de la modif de plannif d'un club (tous les plans sont identiques et dupliqué pour chaque membre)
                $newEvent->setCoachingPlan($coachingPlanRep->find($coachingPlanId));
                $this->_em->persist($newEvent);
                $this->_em->flush();
                $userParticipatesEvent = new \Ks\EventBundle\Entity\UserParticipatesEvent( $newEvent, $userRep->find($newUserId));

                $this->_em->persist($userParticipatesEvent);
                $this->_em->flush();
                $this->_em->persist($newEvent);
                $this->_em->flush();
                
                $newEvent->addUserParticipatesEvent($userParticipatesEvent);//permet de créer ks_event_has_user et ks_user_participates_event
            }
            else {
                //Cas de la duplication d'un event d'un user pour lui même (toujours dans le cadre d'un club
                $newEvent->setCoachingPlan($event->getCoachingPlan());
            }
        }
        
        $this->_em->persist($newEvent);
        $this->_em->flush();
        
        if ((is_null($newUserId) && $event->getCoachingPlan()->getCoachingPlanType()->getCode() == "club") || $event->getCoachingPlan()->getCoachingPlanType()->getCode() != "club") {
            //FMO FIXME ! nécessaire pour générer du ks_event_has_users
            foreach($event->getUsersParticipations() as $userParticipates){
                //var_dump("ici2 : " . $userParticipates->getId());
                $userParticipatesEvent = new \Ks\EventBundle\Entity\UserParticipatesEvent( $newEvent, $userParticipates);

                $this->_em->persist($userParticipatesEvent);
                $this->_em->flush();

                $newEvent->addUserParticipatesEvent($userParticipatesEvent);
            }
            $this->_em->persist($newEvent);
            $this->_em->flush();

            $userParticipatesEventRep   = $this->_em->getRepository('KsEventBundle:UserParticipatesEvent');
            foreach($event->getUsersParticipations() as $userParticipates){
                $userParticipatesEventRep->userParticipatesAnymoreEvent( $newEvent, $userParticipates);
            }
        }
        //var_dump("ici3");
        $this->_em->flush();
        return $newEvent;
    }
    
    public function updateEventForClub( $event ) {
        $eventRep           = $this->_em->getRepository('KsEventBundle:Event');
        
        $startDateTime = $event->getStartDate();
        $endDateTime = $event->getEndDate();
        $club = $event->getClub();

        //Mise à jour de tous les event associés à celui qui a été modifié
        $events = $eventRep->findBy(array("coachingSession" => $event->getCoachingSession()->getId(), "startDate" => $startDateTime, "club" => $club->getId()));
        foreach ($events as $eventToUpdate) {
            $eventToUpdate->setContent($event->getContent());
            $eventToUpdate->setDifficulty($event->getDifficulty());

            $eventToUpdate->setHrCoeffMin($event->getHrCoeffMin());
            $eventToUpdate->setHrCoeffMax($event->gethrCoeffMax());
            $eventToUpdate->setHrType($event->getHrType());
            $eventToUpdate->setIntervalDuration($event->getIntervalDuration());
            $eventToUpdate->setIntervalDistance($event->getIntervalDistance());
            $eventToUpdate->setVMACoeff($event->getVMACoeff());
            $eventToUpdate->setDurationMin($event->getDurationMin());
            $eventToUpdate->setDurationMax($event->getDurationMax());
            $eventToUpdate->setDistanceMin($event->getDistanceMin());
            $eventToUpdate->setDistanceMax($event->getDistanceMax());
            $eventToUpdate->setElevationGainMin($event->getElevationGainMin());
            $eventToUpdate->setElevationGainMax($event->getElevationGainMax());
            $eventToUpdate->setElevationLostMin($event->getElevationLostMin());
            $eventToUpdate->setElevationLostMax($event->getElevationLostMax());
            $eventToUpdate->setSpeedAverageMin($event->getSpeedAverageMin());
            $eventToUpdate->setSpeedAverageMax($event->getSpeedAverageMax());
            $eventToUpdate->setPowMin($event->getPowMin());
            $eventToUpdate->setPowMax($event->getPowMax());

            $eventToUpdate->setSport($event->getSport());
            $eventToUpdate->setTypeEvent($event->getTypeEvent());
            $eventToUpdate->setName($event->getName());
            $eventToUpdate->setIsAllDay($event->getIsAllDay());
            $eventToUpdate->setCreationDate(new \DateTime("Now"));
            $eventToUpdate->setStartDate(new \DateTime($startDateTime->format("Y-m-d H:i:s")));
            $eventToUpdate->setEndDate(new \DateTime($endDateTime->format("Y-m-d H:i:s")));
            $eventToUpdate->setLastModificationDate(new \DateTime("Now"));
            
            $this->_em->persist($eventToUpdate);
        }
        
        $this->_em->flush();
    }
    
    public function removeEventForClub( $event ) {
        $eventRep                   = $this->_em->getRepository('KsEventBundle:Event');
        $userParticipatesEventRep   = $this->_em->getRepository('KsEventBundle:UserParticipatesEvent');
                
        //Suppression de tous les event associés à celui qui a été modifié
        $events = $eventRep->findBy(array("coachingSession" => $event->getCoachingSession()->getId(), "startDate" => $event->getStartDate(), "club" => $event->getClub()->getId()));
        foreach ($events as $eventToDelete) {
            if (is_null($eventToDelete->getActivitySession())) {
                foreach($eventToDelete->getUsersParticipations() as $userParticipates){
                    $userParticipatesEventRep->userParticipatesAnymoreEvent( $eventToDelete, $userParticipates);
                }
                $this->_em->remove($eventToDelete);
            }
        }
        
        $this->_em->flush();
    }
    
    public function moveEventForClub( $event, $dayDelta, $minuteDelta, $isMove) {
        $eventRep                   = $this->_em->getRepository('KsEventBundle:Event');
        $userParticipatesEventRep   = $this->_em->getRepository('KsEventBundle:UserParticipatesEvent');
        $agendaRep                  = $this->_em->getRepository('KsAgendaBundle:Agenda');
                
        //Décalage de tous les event associés à celui qui a été modifié
        $events = $eventRep->findBy(array("coachingSession" => $event->getCoachingSession()->getId(), "startDate" => $event->getStartDate(), "club" => $event->getClub()->getId()));
        foreach ($events as $eventToUpdate) {
            //var_dump($eventToUpdate->getId()."-".$eventToUpdate->getStartDate()->format("Y-m-d H:i:s"));
            if (is_null($eventToUpdate->getActivitySession())) {
                foreach($eventToUpdate->getUsersParticipations() as $userParticipates){
                    if ($event->getUser() != $userParticipates) $agendaRep->moveOrResizeEvent( $eventToUpdate, $dayDelta, $minuteDelta, $isMove);
                }
            }
        }
        
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
