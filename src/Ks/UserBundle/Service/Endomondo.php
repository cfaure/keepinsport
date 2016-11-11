<?php

namespace Ks\UserBundle\Service;

/**
 * Description of Endomondo
 *
 * @author Clem
 */
class Endomondo
{
    protected $_doctrine;
    protected $_curlOptions;
    protected $_curl;
    //protected $_curlCookie;
    protected $_authToken;
    
    const URL_AUTH      = 'https://api.mobile.endomondo.com/mobile/auth';
    const URL_WORKOUTS  = 'http://api.mobile.endomondo.com/mobile/api/workout/list';
    const URL_TRACK     = 'http://api.mobile.endomondo.com/mobile/readTrack';
    const URL_PLAYLIST  = 'http://api.mobile.endomondo.com/mobile/api/workout/playlist';
    
    /**
     *
     * @param Registry $doctrine
     * @param Ks\ActivityBundle\Service\ImportActivityService $importActivityService 
     */
    public function __construct(
        \Symfony\Bundle\DoctrineBundle\Registry $doctrine,
        \Ks\ActivityBundle\Service\ImportActivityService $importActivityService
    ) {
        $this->_doctrine            = $doctrine;
        $this->_importActivityService = $importActivityService;
        $this->_curlOptions         = array(
            CURLOPT_FOLLOWLOCATION  => 1,
            CURLOPT_RETURNTRANSFER  => 1,
            CURLOPT_HTTPGET         => 1,
            CURLOPT_POST        	=> 0,
            //CURLOPT_CAINFO          => getcwd() . DIRECTORY_SEPARATOR . "quick-import" . DIRECTORY_SEPARATOR . "resources" . DIRECTORY_SEPARATOR . "mozilla.pem",
            CURLOPT_HEADER          => 1,
            //CURLOPT_USERAGENT       => 'Mozilla/5.0 (Windows; U; Windows NT 6.1; fr; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12',
            CURLOPT_SSL_VERIFYPEER	=> 0,
            CURLOPT_SSL_VERIFYHOST	=> 2
        );
        $this->_curl        = curl_init();
        //$this->_curlCookie  = tempnam(sys_get_temp_dir(), 'endomondocookie');
        $this->_authToken   = null;
    }
    
    /**
     *
     * @return string 
     */
    public function getName()
    {
        return 'Endomondo';
    }
    
    /**
     * Récupère le token d'authenfication de l'api mobile endomondo
     * 
     * @param string $email
     * @param string $password
     * @return boolean Retourne true ou false si l'authentification a échoué
     */
    public function authenticate($email, $password)
    {
        $matches        = array();
        $qParams        = http_build_query(array(
            'v'         => '2.4',
            'action'    => 'PAIR',
            'deviceId'  => 1,
            'country'   => 'en',
            'email'     => $email,
            'password'  => $password
        ));
        curl_setopt_array($this->_curl, array(
            CURLOPT_HEADER  => 0,
            CURLOPT_URL     => self::URL_AUTH.'?'.$qParams
        ) + $this->_curlOptions);
                
        $ret = curl_exec($this->_curl);
        if (preg_match('/authToken=(.+)/', $ret, $matches)) {
            $this->_authToken = $matches[1];
            return true;
        }
        
        return false;
    }
    
    /**
     * 
     * @param string $token
     */
    public function setAuthToken($token)
    {
        $this->_authToken = $token;
    }
    
    /**
     * 
     * @return type
     */
    public function getAuthToken()
    {
        return $this->_authToken;
    }
    
    /**
     * Retourne l'intitulé du sport à partir de son id
     * @param int $endomondoSportId
     * @return string
     */
    public function getSportLabel($endomondoSportId)
    {
        $sportsMap = array(
            0    => 'Running',
            1    => 'Cycling', //'Cycling, transport',
            2    => 'Cycling', //'Cycling, sport',
            3    => 'Mountain-biking', //'Mountain biking',
            4    => 'skateboard',
            5    => 'Roller skiing',
            6    => 'crossCountrySkiing', //'Skiing, cross country',
            7    => 'Ski', //'Skiing, downhill',
            8    => 'Snowboard', //'Snowboarding',
            9    => 'Canoe Kayak', //'Kayaking',
            10   => 'Kite surfing',
            11   => 'Rowing',
            12   => 'Voile', //'Sailing',
            13   => 'Windsurfing',
            14   => 'Fitness walking',
            15   => 'Golfing',
            16   => 'hiking', //'Hiking',
            17   => 'Orienteering',
            18   => 'Walking',
            19   => 'Riding',
            20   => 'Swimming',
            21   => 'Spinning',
            22   => 'climbing',
            23   => 'Aerobics',
            24   => 'Badminton',
            25   => 'Baseball',
            26   => 'Basketball',
            27   => 'Boxing',
            28   => 'climbing_stairs',
            29   => 'Cricket',
            30   => 'cross-training',
            31   => 'Dancing',
            32   => 'Fencing',
            33   => 'Football, American',
            34   => 'Rugby', //'Football, rugby',
            35   => 'Football', //'Football, soccer',
            36   => 'Handball',
            37   => 'Hockey',
            38   => 'Pilates',
            39   => 'Polo',
            40   => 'Scuba diving',
            41   => 'Squash',
            42   => 'Table tennis',
            43   => 'Tennis',
            44   => 'Volley', //'Volleyball, beach',
            45   => 'Volley', //'Volleyball, indoor',
            46   => 'Weight training',
            47   => 'Yoga',
            48   => 'Martial arts',
            49   => 'Gymnastics',
            50   => 'step-counter'
        );
        
        if (array_key_exists($endomondoSportId, $sportsMap)) {
            return $sportsMap[$endomondoSportId];
        }
        
        return $sportsMap[22]; // 'other';
    }
    
    /**
     * Récupère la liste de toutes les activtités sportives de l'utilisateur
     * NOTE CF: vérifier que l'attribut "more" de la réponse est toujours à false
     * 
     * @return array
     */
    public function fetchWorkouts($limit = null)
    {
        $workouts = array();
        curl_setopt_array($this->_curl, array(
            CURLOPT_HEADER		=> 0,
            //CURLOPT_URL         => self::URL_WORKOUTS.'?authToken='.$this->_authToken
            CURLOPT_URL         => self::URL_WORKOUTS.'?authToken='.$this->_authToken.'&maxResults='.$limit
        ) + $this->_curlOptions);
        $ret = curl_exec($this->_curl);
        
        if ($ret) {
            $res = json_decode($ret, true);
            if (isset($res['data'])) {
                foreach ($res['data'] as $workout) {
                    // on indexe sur l'id
                    $workouts[$workout['id']] = $workout;
                }
            }
        }
        return $workouts;
    }
    
    /**
     * Récupère les points de tracking gps d'une activité.
     * 
     * @param int $trackId
     * @return array
     */
    public function fetchTrackPoints($trackId)
    {
        $qParams    = http_build_query(array(
            'authToken' => $this->_authToken,
            'trackId'   => $trackId
        ));
        curl_setopt_array($this->_curl, array(
            CURLOPT_HEADER  => 0,
            CURLOPT_URL     => self::URL_TRACK.'?'.$qParams
        ) + $this->_curlOptions);
        $ret    = curl_exec($this->_curl);
        $lines  = explode("\n", $ret);
        $points = array();

        foreach ($lines as $line) {
            if ($line == 'OK') continue;
            // 0    2012-06-02 02:58:45 UTC     timestamp
            // 1    2                           endomondoSportId?
            // 2    45.743754                   lat
            // 3    4.872521                    lon
            // 4    ??
            // 5    ??
            // 6    177.0                       alt
            // 7    ??
            // 8    ??
            $cols       = explode(';', $line);
            if (count($cols) !== 9) {
                // second line and last line
                continue;
            }
            $points[]   = array(
                'timestamp' => strtotime($cols[0]),
                'lat'       => $cols[2],
                'lon'       => $cols[3],
                'ele'       => $cols[6]
            );
        }
        
        return $points;
    }
    
    /**
     * @deprecated Méthode web
     * @param type $email
     * @param type $password 
     */
    public function connect($email, $password)
    {
        curl_setopt_array($this->_curl, array(
            CURLOPT_HTTPGET		=> 0,
            CURLOPT_POST		=> 1,
            CURLOPT_POSTFIELDS	=> http_build_query(array(
                'email'         => $email,
                'password'      => $password
            )),
            CURLOPT_URL			=> 'https://www.endomondo.com/access?wicket:interface=:42:pageContainer:lowerSection:lowerMain:lowerMainContent:signInPanel:signInFormPanel:signInForm::IFormSubmitListener::',
            CURLOPT_COOKIEFILE	=> $this->_curlCookie,
            CURLOPT_COOKIEJAR	=> $this->_curlCookie,
            //CURLOPT_REFERER		=> 'https://www.endomondo.com/access'
        ) + $this->_curlOptions);
        
        curl_exec($this->_curl);
    }
    
    /**
     * @deprecated Methode web
     * @return type
     */
    public function getActivities()
    {
        $matches = array();
        
        curl_setopt_array($this->_curl, array(
            CURLOPT_URL			=> 'www.endomondo.com/workouts/list',
            CURLOPT_COOKIEFILE	=> $this->_curlCookie,
        ) + $this->_curlOptions);
        $ret = curl_exec($this->_curl);

        preg_match_all('/:results:results:([0-9]+):cells:4::IBehaviorListener/', $ret, $matches);
        
        if (count($matches) != 2) {
            return array();
        }
        foreach ($matches[1] as $match) {
            curl_setopt_array($this->_curl, array(
                CURLOPT_URL         => 'www.endomondo.com/workouts/list/../../'
                    .'?wicket:interface=:0:pageContainer:lowerSection:lowerMain:lowerMainContent:results:results:'
                    .$match
                    .':cells:4::IBehaviorListener:0:2',
                CURLOPT_COOKIEFILE	=> $this->_curlCookie,
                CURLOPT_NOBODY      => 1
            ) + $this->_curlOptions);
            curl_exec($this->_curl);
            $ret = curl_getinfo($this->_curl);
            return $ret;
        }
                
        return array();
        //return $matches;
    }
    
    /**
     * @deprecated Méthode web
     * @param type $activityId 
     */
    public function importGpx($activityId)
    {
        $tmpGpx = tempnam(sys_get_temp_dir(), '');
        $fh     = fopen($tmpGpx, 'wb');
        curl_setopt_array($this->_curl, array(
            CURLOPT_URL			=> 'http://www.endomondo.com/workouts/'.$activityId.'/../../?wicket:interface=:40:pageContainer:lightboxContainer:lightboxContent:exportPanel:exportGpxLink:1:IResourceListener::',
            //CURLOPT_URL			=> 'www.endomondo.com/workouts/list/../../?wicket:interface=:30:pageContainer:lowerSection:lowerMain:lowerMainContent:results:results:1:cells:2::IBehaviorListener:0:2',
            //CURLOPT_URL			=> 'http://www.endomondo.com/home',
            CURLOPT_COOKIEFILE	=> $this->_curlCookie,
            CURLOPT_FILE        => $fh,
            CURLOPT_HEADER      => 0,
        ) + $this->_curlOptions);
        curl_exec($this->_curl);
        
        $json = $this->_importActivityService->buildJsonToSave(array('fileName' => $tmpGpx), 'endomondo');
                
        fclose($fh);
    }
}

?>
