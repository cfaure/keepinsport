<?php

namespace Ks\ActivityBundle\Twig\Extensions;


class KsActivityExtension extends \Twig_Extension
{
    protected $container;
    
    public function __construct($container)
    {
        $this->container        = $container;
    }
    
    public function getFilters()
    {
        return array(
            'total_points'              => new \Twig_Filter_Method($this, 'sumUserPointsSinceBeginningOfSeason'),
            'of'                        => new \Twig_Filter_Method($this, 'pointsOf'),
            'last_mofified_content'     => new \Twig_Filter_Method($this, 'getLastModifiedContent'),
            'has_tag'                   => new \Twig_Filter_Method($this, 'articleHasTag'),
            'to_string'                 => new \Twig_Filter_Method($this, 'objectArrayToString'),
            'is_subscribed'             => new \Twig_Filter_Method($this, 'userIsSubscribed'),
            'has_voted'                 => new \Twig_Filter_Method($this, 'userHasVoted'),
            'has_warned'                => new \Twig_Filter_Method($this, 'userHasWarnedLikeDisturbing'),
            'get_tag'                   => new \Twig_Filter_Method($this, 'getTag'),
            'categories'                => new \Twig_Filter_Method($this, 'getArticleCategories'),
            'addslashes'                => new \Twig_Filter_Method($this, 'twig_addslashes'),
            'is_a_training_plan'        => new \Twig_Filter_Method($this, 'isTrainingPlan'),
            'is_a_sport_event'          => new \Twig_Filter_Method($this, 'isSportEvent'),
            'nikePlus'                  => new \Twig_Filter_Method($this, 'getNikePlusRunInfos'),
            'runKeeper'                 => new \Twig_Filter_Method($this, 'getRunKeeperRunInfos'),
            'suunto'                    => new \Twig_Filter_Method($this, 'getSuuntoRunInfos'),
            'run_duration'              => new \Twig_Filter_Method($this, 'millisecondesToTimeDuration'),
            'seconds'                   => new \Twig_Filter_Method($this, 'dateTimeToSeconds'),
            'extract'                   => new \Twig_Filter_Method($this, 'extractFromGpxFile'),
            'issetImg'                  => new \Twig_Filter_Method($this, 'issetImg'),
            'negative_karma'            => new \Twig_Filter_Method($this, 'negativeKarma'),
            'method_exist'            => new \Twig_Filter_Method($this, 'negativeKarma'),
            'split_number'            => new \Twig_Filter_Method($this, 'split_number'),
            'removeNewLineCharacter'            => new \Twig_Filter_Method($this, 'removeNewLineCharacter'),
        );
    }

    public function sumUserPoints($activitySessionEarnsPoints)
    {
        $sum = 0;
        foreach($activitySessionEarnsPoints as $value) {
            if(! $value->getActivitySession()->getIsDisabled() && $value->getActivitySession()->getIsValidate() ) {
                $sum += $value->getPoints();
            }
        }
        return intval($sum);
    }
    
    /**
     *
     * @param type $activitySessionEarnsPoints
     * @return int 
     */
    public function sumUserPointsSinceBeginningOfSeason($activitySessionEarnsPoints)
    {
        $beginSeasonDateFr = $this->container->getParameter('beginSeasonDateFr');
        if (!$beginSeasonDateFr) {
            return 0;
        }
        
        $beginningOfSeason  = \DateTime::createFromFormat('d/m/Y H:i:s', $beginSeasonDateFr);
        $sum                = 0;
        foreach($activitySessionEarnsPoints as $value) {
            $activitySession = $value->getActivitySession();
            if( $activitySession->getIssuedAt() > $beginningOfSeason && !$activitySession->getIsDisabled() && $activitySession->getIsValidate() ) {
                $sum += $value->getPoints();
            }
        }
        
        return (int)$sum;
    }
    
    public function pointsOf($activitySessionEarnedPoints, $user)
    {
        $pointsOf = 0;
        foreach( $activitySessionEarnedPoints as $value) {
            if(! $value->getActivitySession()->getIsDisabled() ) {
                if ($value->getUser()->getId() == $user->getId()) {
                    $pointsOf = $value->getPoints();
                }
            }
        }
        return intval($pointsOf);
    }
    
    // FIXME: pas terrible en terme de perfs... il faut chainer les entrées dans la table des modifications
    //   avec un pointeur sur la modif précédente, et à null pour la dernière entrée
    public function getLastModifiedContent($articleModifications, $infoToGet)
    {
        $content = "";
        $lastModificationDate = null;
        $return = "";
        
        foreach($articleModifications as $modification) {
            if ($lastModificationDate == null) {
                $lastModificationDate = $modification->getModifiedAt();
                $content = $modification->getContent();
                
            } elseif ($modification->getModifiedAt() > $lastModificationDate) {
                    $lastModificationDate = $modification->getModifiedAt();
                    $content = $modification->getContent();
                    
            }
        }
        
        $content = json_decode( $content );
        switch ( $infoToGet ) {
            case "title":
                if( isset( $content->title )) $return = base64_decode( $content->title );
                else $return = null;
                break;
            
            case "description":
                $return =  base64_decode( $content->description );
                break;
            
            case "tags":
                $return = $content->tags;
                break;
        }

        return $return;
    }
    
    public function articleHasTag($articleTagsId, $tag)
    {
        $articleHasTag = false;
        
        foreach( $articleTagsId as $articleTagId ) {
            if( $articleTagId == $tag->getId() ) {
                $articleHasTag = true;
            }
        }
        
        return $articleHasTag;
    }
    
    //Tranforme un array d'objet en chaine avec une virgule. $propertyName contient le nom du champ à afficher dans la chaine
    public function objectArrayToString($array, $propertyName)
    {
        $string = "";
        
        foreach( $array as $key => $element ) {
            $get = "get".ucfirst($propertyName);
            
            if ( $key != count($array) - 1 ) {
                $string .= $element->$get().", ";
            } else {
                $string .= $element->$get();
            }
        }
        
        return $string;
    }
    
    /**
     * TODO: à passer dans la requête qui récupère les données des activités
     * 
     * @param int $activityId
     * @param int $userId
     * @return boolean 
     */
    public function userIsSubscribed($activityId, $userId)
    {
        $dbh    = $this->container->get('doctrine')->getEntityManager()->getConnection();
        $sql    = 'select count(subscriber_id)'
            .' from ks_activity_has_subscribers where 1'
            .' and (hasUnsubscribed is null or hasUnsubscribed = 0)'
            .' and subscriber_id = :userId'
            .' and activity_id = :activityId';
        $stmt   = $dbh->executeQuery(
            $sql,
            array(
                'userId'        => $userId,
                'activityId'    => $activityId
            )
        );
        
        return $stmt->fetchColumn() > 0 ? true : false;
    }
    
    /**
     *
     * @param type $activityId
     * @param type $userId
     * @return type 
     */
    public function userHasVoted($activityId, $userId)
    {
        $dbh    = $this->container->get('doctrine')->getEntityManager()->getConnection();
        $sql    = "select count(*) from ks_activity_has_votes where 1"
            ." and voter_id = :userId and activity_id = :activityId";
        $stmt   = $dbh->executeQuery($sql, array('userId' => $userId, 'activityId'  => $activityId));
        
        return $stmt->fetchColumn() > 0 ? true : false;
    }
    
    /**
     *
     * @param type $activityId
     * @param type $userId
     * @return boolean 
     */
    public function userHasWarnedLikeDisturbing($activityId, $userId)
    {
        $dbh    = $this->container->get('doctrine')->getEntityManager()->getConnection();
        $sql    = "select count(*) from ks_activity_is_disturbing where 1"
            ." and user_id = :userId and activity_id = :activityId";
        $stmt   = $dbh->executeQuery($sql, array('userId' => $userId, 'activityId'  => $activityId));
                
        return $stmt->fetchColumn() > 0 ? true : false;
    }
    
    /**
     *
     * @param type $tags
     * @param type $tagId
     * @return type 
     */
    public function getTag($tags, $tagId)
    {
        $return = array("find" => false, "tag" => null);
        
        foreach( $tags as $tag ) {
            if( $tag->getId() == $tagId) {
                $return["find"] = true;
                $return["tag"]  = $tag;
            }
        }
        
        return $return;
    }
    
    public function getArticleCategories($tags, $tagsId)
    {
        $return = array("find" => false, "tags" => array());
        
        foreach( $tagsId as $tagId ) {
            foreach( $tags as $tag ) { 
                if( $tag->getId() == $tagId && $tag->getIsCategory() ) {
                    $return["find"] = true;
                    $return["tags"][]  = $tag;
                }
            }
        }
        
        return $return;
    }
    
    function twig_addslashes($value) {	
        return addslashes($value);
    }
    
    function isTrainingPlan($tags, $tagsId) {
        
        foreach( $tags as $tag ) {
            foreach( $tagsId as $tagId ) {
                if( $tag->getId() == $tagId) {
                    if( $tag->getLabel() == "Programme Entrainement") {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    function isSportEvent($tags, $tagsId) {
        
        foreach( $tags as $tag ) {
            foreach( $tagsId as $tagId ) {
                if( $tag->getId() == $tagId) {
                    if( $tag->getLabel() == "Evénement Sportif") {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    function getNikePlusRunInfos($nikeSourceActivity, $infoToGet) {

        $activity = json_decode($nikeSourceActivity);
        $return = array();
        
        switch( $infoToGet ) {
            case "waypoints":
                if (isset( $activity->geo->waypoints )) {
                    $return = $activity->geo->waypoints;
                } 
                break;
            
            case "coordinate":
                if (isset( $activity->geo->coordinate )) {
                    $coordinate = $activity->geo->coordinate;
                    $coordinates = explode(',', $coordinate);
                    $lat = trim($coordinates[0]);
                    $lon = trim($coordinates[1]);
                    $return = array(
                        'lat' => $lat,
                        'lon' => $lon
                    );
                } 
                break;
           case "KMSPLIT":
                if (isset( $activity->snapshots->KMSPLIT->datasets )) {
                    $return = $activity->snapshots->KMSPLIT->datasets;
                } 
                break;
           case "history":
                if (isset( $activity->history )) {
                    $history = array();
                    
                    foreach( $activity->history as $value ) {
                        switch( $value->type ) {
                            case "CADENCE":
                                $history["cadence"]     = $value;
                                break;
                            case "SPEED":
                                $history["speed"]       = $value;
                                break;
                            case "DISTANCE":
                                $history["distance"]    = $value;
                                break;
                        }
                    }
                    
                    $return = $history;
                } 
                break;
           default:
               $return = array();
        }   
        
        return $return;
    }
    
    function getRunKeeperRunInfos($runkeeperSourceActivity, $infoToGet) {

        $activity = json_decode($runkeeperSourceActivity);
        $return = array();
        
        switch( $infoToGet ) {
            case "waypoints":
                $waypoints = array();
                if (isset( $activity->path )) {
                    foreach( $activity->path as $waypoint ) {
                        $waypoints[] = array(
                            "lat"       => $waypoint->latitude,
                            "lon"       => $waypoint->longitude,
                            "ele"       => $waypoint->altitude,
                            "timestamp" => $waypoint->timestamp
                        );
                    }
                } 
                $return = $waypoints;
                break;
            
           default:
               $return = array();
        }
        
        return $return;
    }
    
    function getSuuntoRunInfos($suuntoSourceActivity, $infoToGet) {

        $activity = json_decode($suuntoSourceActivity);
        $return = array();
        
        switch( $infoToGet ) {
            case "waypoints":
                $waypoints = array();
                if (isset( $activity->path )) {
                    foreach( $activity->path as $waypoint ) {
                        $waypoints[] = array(
                            "lat"       => $waypoint->latitude,
                            "lon"       => $waypoint->longitude,
                            "ele"       => $waypoint->altitude,
                            "timestamp" => $waypoint->timestamp
                        );
                    }
                } 
                $return = $waypoints;
                break;
            
           default:
               $return = array();
        }
        
        return $return;
    }
    
    function extractFromGpxFile($gpxFileName, $infoToGet) {
        $return = array();
        $gpsFileAbsolutePath = $_SERVER['DOCUMENT_ROOT'] . $gpxFileName;
        
        switch( $infoToGet ) {
            case "waypoints":
                $waypoints = array();
                if ( file_exists($gpsFileAbsolutePath) ) {
                    $xml = simplexml_load_file($gpsFileAbsolutePath);
                    
                    $i = 0;

                   foreach($xml->trk->trkseg as $segment){
                        //$trackPoints = $xml->getElementsByTagName('trkpt');
                        foreach ($segment->trkpt as $trackpoint) {

                            //$attributs = $trkpt->attributes();
                            /*$str = "";
                            foreach($attributs as $index => $contenu) {
                                $str .= "[<strong>".$index."</strong>] <em>".$contenu."</em>, ";
                            }
                            return $str;
                            var_dump($attributes);*/
                            
                            //$i = $i+1;
                            $timeWaypoint = new \DateTime($trackpoint->time);
                            $waypoints[] = array(
                                "lat"       => floatval($trackpoint["lat"]),
                                "lon"       => floatval($trackpoint["lon"]),
                                "ele"       => floatval($trackpoint->ele),
                                "timestamp" => $timeWaypoint->getTimestamp()
                            );
                            
                            
                            //$ele = $trkpt->getElementsByTagName('ele')->item(0);
                            /*$waypoints[] = array(
                                "lat" => $trkpt->attributes()["lat"],
                                "lon" => $trkpt->attributes()["lon"],
                                //"ele" => $ele
                            );*/
                             /*$i = $i+1;
                            $waypoints[$i]["lat"] = floatval($trackpoint["lat"]);
                            $waypoints[$i]["lon"] = floatval($trackpoint["lon"]);
                            $waypoints[$i]["ele"] = floatval($trackpoint->ele);*/
                            //$waypoints[$i]["time"] = $trackpoint->time;

                            
                        }
                   }
                    
                }
                $return = $waypoints;
                break;
        }
        
        return $return;
        
        
    }
    
    function issetImg($imgFileName) {
        $imgFileAbsolutePath = $_SERVER['DOCUMENT_ROOT'] . $imgFileName;
        
        return file_exists($imgFileAbsolutePath);   
    }
    
    public function negativeKarma( $activities )
    {
        $negative_karma = 0;
        foreach( $activities as $activity ) {
           $negative_karma += count ( $activity->getUsersWhoWarnedLikeDisturbing() );
        }
        return intval( $negative_karma );
    }
    
    function millisecondesToTimeDuration($duration) {
        $duration /= 1000;
        $heure = intval(abs($duration / 3600));
        $duration = $duration - ($heure * 3600);
        $minute = intval(abs($duration / 60));
        $duration = $duration - ($minute * 60);
        $seconde = round($duration);
        $time = new \DateTime("$heure:$minute:$seconde");
        //$time = "$heure:$minute:$seconde";
        return $time;
    }
    
    function dateTimeToSeconds(\DateTime $dateTime) {
         $hours     = $dateTime->format('H');
         $minutes   = $dateTime->format('i');
         $seconds   = $dateTime->format('s');
         
         return $seconds + 60 * $minutes + $hours * 60 * 60;
    }
    
    function split_number($number) {       
         return str_split((string)$number);
    }
    
    public function removeNewLineCharacter($value)
    {
        return str_replace(array("\r\n", "\r", "\n"), " ", $value); 
    }

    public function getName()
    {
        return 'ks_activity_extension';
    }
}
?>
