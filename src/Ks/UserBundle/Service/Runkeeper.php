<?php

namespace Ks\UserBundle\Service;

use Symfony\Bundle\DoctrineBundle\Registry;
use Ks\UserBundle\User;

/**
 * Description of Runkeeper
 *
 * @author Clem
 */
class Runkeeper
    extends \Twig_Extension
{
    protected $_doctrine;
    protected $_clientId;
    protected $_clientSecret;
    protected $_authUrl;
    protected $_accessTokenUrl;
    protected $_accessToken;
    
    const API_BASE_URL = 'http://api.runkeeper.com';
    
    /**
     *
     * @param Registry $doctrine
     * @param type $clientId
     * @param type $clientSecret
     * @param type $authUrl
     * @param type $accessTokenUrl 
     */
    public function __construct(Registry $doctrine, $clientId, $clientSecret, $authUrl, $accessTokenUrl)
    {
        $this->_doctrine            = $doctrine;
        $this->_clientId            = $clientId;
        $this->_clientSecret        = $clientSecret;
        $this->_authUrl             = $authUrl;
        $this->_accessTokenUrl      = $accessTokenUrl;
        $this->_accessToken         = null;
    }
    
    // La méthode getName(), obligatoire
    public function getName()
    {
        return 'Runkeeper';
    }
	
    // La méthode getFunctions(), qui retourne un tableau avec les fonctions qui peuvent être appelées depuis cette extension
    public function getFunctions()
    {
        return array(
            'runkeeperRegisterUrl' => new \Twig_Function_Method($this, 'registerUrl') 
        );
    }
    
    /**
     * Given a valid authorisation code, get an access token that will
     * link to user runkeeper's profile.
     * 
     * Make a POST request to the Health Graph API token endpoint.
     * Include the following parameters in application/x-www-form-urlencoded format:
     *    grant_type: The keyword 'authorization_code'
     *    code: The authorization code returned in step 2
     *    client_id: The unique identifier that your application received upon registration
     *    client_secret: The secret that your application received upon registration
     *    redirect_uri: The exact URL that you supplied when sending the user to the authorization endpoint above
     * 
     * 
     * @param type $authCode 
     */
    public function getAccessToken($authCode, $redirectUri)
    {
        if ($authCode == '') {
            throw new \Exception('AuthCode non valide');
        }
        
        $curl   = curl_init();
        $params = http_build_query(array(
            'grant_type'    => 'authorization_code',
            'code'          => $authCode,
            'client_id'     => $this->_clientId,
            'client_secret' => $this->_clientSecret,
            'redirect_uri'  => $redirectUri
        ));
        $options = array(
            CURLOPT_URL             => $this->_accessTokenUrl,
            CURLOPT_POST            => true,
            CURLOPT_POSTFIELDS      => $params,
            CURLOPT_RETURNTRANSFER  => true
        );
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // Eviter les problèmes de validations de certificats SSL
        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        curl_close($curl);
        
        $decoderesponse = json_decode($response);
        if ( ($decoderesponse == null) || isset($decoderesponse->error) ) {
            $error = $decoderesponse->error != '' ? $decoderesponse->error : 'Réponse vide';
            throw new \Exception($error);
        }
        
        if (isset($decoderesponse->access_token) && $decoderesponse->access_token != '') {
            $this->_accessToken = $decoderesponse->access_token;
        } else {
            throw new \Exception('Réponse ok mais Access Token vide');
        }
//        if ($decoderesponse->token_type) {
//            $tokenType = $decoderesponse->token_type;
//        }
        
        return $this->_accessToken;
    }

    /**
     *
     * @param type $callbackUri
     * @return type 
     */
    public function registerUrl($callbackUri)
    {
        return 'http://runkeeper.com/apps/authorize'
            .'?client_id='.$this->_clientId
            .'&redirect_uri='.$callbackUri
            .'&response_type=code';
    }
    
    /**
     * Get access token associated to service instance.
     * 
     * @param string $token 
     */
    public function setAccessToken($token)
    {
        $this->_accessToken = $token;
    }
    
    /**
     *
     * @param string $nodePath
     * @return boolean 
     */
    public function requestHealthGraph($nodePath, $numpage = 0 , $pageSize = 25, $noEarlierThan = '1970-01-01', $noLaterThan = '', $modifiedNoEarlierThan = '1970-01-01', $modifiedNoLaterThan = '')
    {
        
        $dateTime = new \DateTime('now');
        $today = $dateTime->format('Y-m-d');
        $noLaterThan = $today;
        $modifiedNoLaterThan = $today;

        $nodeTypes = array(
            '/user'                          => 'application/vnd.com.runkeeper.User+json',
            //'/fitnessActivities'             => 'application/vnd.com.runkeeper.LiveFitnessActivityCompletion+json',
            '/fitnessActivities'             => 'application/vnd.com.runkeeper.FitnessActivity+json',
            	
     
            '/strengthTrainingActivities'    => 'application/vnd.com.runkeeper.StrengthTrainingActivityFeed+json',
            '/weight'                        => 'application/vnd.com.runkeeper.WeightFeed+json',
            '/settings'                      => 'application/vnd.com.runkeeper.Settings+json',
            '/diabetes'                      => 'application/vnd.com.runkeeper.DiabetesFeed+json',
            '/team'                          => 'application/vnd.com.runkeeper.TeamFeed+json',
            '/sleep'                         => 'application/vnd.com.runkeeper.SleepFeed+json',
            '/nutrition'                     => 'application/vnd.com.runkeeper.NutritionFeed+json',
            '/generalMeasurements'           => 'application/vnd.com.runkeeper.GeneralMeasurementFeed+json',
            '/backgroundActivities'          => 'application/vnd.com.runkeeper.BackgroundActivityFeed+json',
            '/records'                       => 'application/vnd.com.runkeeper.Records+json',
            '/profile'                       => 'application/vnd.com.runkeeper.Profile+json',
        );
        $options = array(
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$this->_accessToken,
                //'Accept: '.$nodeTypes[$nodePath]
            ),
            CURLOPT_URL             => self::API_BASE_URL.$nodePath."?page=$numpage&pageSize=$pageSize&noEarlierThan=$noEarlierThan&noLaterThan=$noLaterThan&modifiedNoEarlierThan=$modifiedNoEarlierThan&modifiedNoLaterThan=$modifiedNoLaterThan",
            CURLOPT_RETURNTRANSFER  => true,
            CURLINFO_HEADER_OUT     => true,
        );
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); /* Added to avoid "error :SSL certificate problem, verify that the CA cert is OK" */
        curl_setopt_array($curl, $options);
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, array(&$this, 'parseHeader')); /* add callback header function to process response headers */
        $response       = curl_exec($curl);
        $responsecode   = curl_getinfo($curl,CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($responsecode === 200) {
            $response       = htmlentities($response,ENT_NOQUOTES);
            $decoderesponse = json_decode($response);
            
            return $decoderesponse;
        } elseif (in_array($responsecode, array('201','204','301','304'))) {
            //$this->api_request_log[] = array('name' => $name, 'type' => $type, 'result' => $responsecoce, 'time' => microtime(true)-$orig);
            return true;
        } else {
            // $this->api_last_error = "doRunkeeperRequest: request error => 'name' : ".$name.", 'type' : ".$type.", 'result' : ".$responsecode.", '".$name."' => ".$url;
            // $this->api_request_log[] = array('name' => $name, 'type' => $type, 'result' => 'error : '.$responsecode, 'time' => microtime(true)-$orig);
            return false;
        }
    }
    
    /**
     *
     * @param string $nodePath
     * @return boolean 
     */
    public function requestJSONHealthGraph($nodePath,$numpage = 0 , $pageSize = 25, $noEarlierThan = '1970-01-01', $noLaterThan = '2015-12-01', $modifiedNoEarlierThan = '1970-01-01', $modifiedNoLaterThan = '2015-12-01')
    {
        $nodeTypes = array(
            '/user'                          => 'application/vnd.com.runkeeper.User+json',
            '/fitnessActivities'             => 'application/vnd.com.runkeeper.LiveFitnessActivityCompletion+json',
            '/strengthTrainingActivities'    => 'application/vnd.com.runkeeper.StrengthTrainingActivityFeed+json',
            '/weight'                        => 'application/vnd.com.runkeeper.WeightFeed+json',
            '/settings'                      => 'application/vnd.com.runkeeper.Settings+json',
            '/diabetes'                      => 'application/vnd.com.runkeeper.DiabetesFeed+json',
            '/team'                          => 'application/vnd.com.runkeeper.TeamFeed+json',
            '/sleep'                         => 'application/vnd.com.runkeeper.SleepFeed+json',
            '/nutrition'                     => 'application/vnd.com.runkeeper.NutritionFeed+json',
            '/generalMeasurements'           => 'application/vnd.com.runkeeper.GeneralMeasurementFeed+json',
            '/backgroundActivities'          => 'application/vnd.com.runkeeper.BackgroundActivityFeed+json',
            '/records'                       => 'application/vnd.com.runkeeper.Records+json',
            '/profile'                       => 'application/vnd.com.runkeeper.Profile+json',
        );
        $options = array(
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$this->_accessToken,
                //'Accept: '.$nodeTypes[$nodePath]
            ),
            CURLOPT_URL             => self::API_BASE_URL.$nodePath."?page=$numpage&pageSize=$pageSize&noEarlierThan=$noEarlierThan&noLaterThan=$noLaterThan&modifiedNoEarlierThan=$modifiedNoEarlierThan&modifiedNoLaterThan=$modifiedNoLaterThan",
            //CURLOPT_URL             => self::API_BASE_URL.$nodePath,
            CURLOPT_RETURNTRANSFER  => true,
            CURLINFO_HEADER_OUT     => true,
        );
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); /* Added to avoid "error :SSL certificate problem, verify that the CA cert is OK" */
        curl_setopt_array($curl, $options);
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, array(&$this, 'parseHeader')); /* add callback header function to process response headers */
        $response       = curl_exec($curl);
        $responsecode   = curl_getinfo($curl,CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($responsecode === 200) {
            $response       = htmlentities($response,ENT_NOQUOTES);
            //$decoderesponse = json_decode($response);
            
            return $response;
        } elseif (in_array($responsecode, array('201','204','301','304'))) {
            //$this->api_request_log[] = array('name' => $name, 'type' => $type, 'result' => $responsecoce, 'time' => microtime(true)-$orig);
            return true;
        } else {
            //return $response;
            // $this->api_last_error = "doRunkeeperRequest: request error => 'name' : ".$name.", 'type' : ".$type.", 'result' : ".$responsecode.", '".$name."' => ".$url;
            // $this->api_request_log[] = array('name' => $name, 'type' => $type, 'result' => 'error : '.$responsecode, 'time' => microtime(true)-$orig);
            return false;
        }
    }
    
    
    
    
    
    
    /**
     *
     * @return type 
     */
    public function getFitnessActivities($numpage = 0 , $pageSize = 25, $noEarlierThan = '1970-01-01', $noLaterThan = '2012-07-07', $modifiedNoEarlierThan = '1970-01-01', $modifiedNoLaterThan = '2012-07-07')
    {
        $userNode   = $this->requestHealthGraph('/user');
        $curNode    = $this->requestHealthGraph($userNode->fitness_activities);// ,$numpage , $pageSize , $noEarlierThan , $noLaterThan , $modifiedNoEarlierThan , $modifiedNoLaterThan );
        $activities = array();
        
        while ($curNode && isset($curNode->items) && is_array($curNode->items) && count($curNode->items) > 0) {
            foreach ($curNode->items as $item) {
                $activity           = (array)$item;
                //$activityDetails    = $this->requestHealthGraph($item->uri);
                $activities[]       = $activity;//array_merge($activity, array('total_calories' => $activityDetails->total_calories));
                //break;
            }
            if (isset($curNode->next) && !empty($curNode->next)) {
                $curNode = $this->requestHealthGraph($curNode->next);
            } else {
                $curNode = null;
            }
        }
        
        return $activities;
    }
    
    public function getFitnessActivity($uri)
    {
        $options = array(
            CURLOPT_HTTPHEADER => array(
                'Host: api.runkeeper.com',
                'Authorization: Bearer '.$this->_accessToken,
                'Accept: application/vnd.com.runkeeper.FitnessActivity+json'
            ),
            CURLOPT_URL             => self::API_BASE_URL.$uri,
            CURLOPT_RETURNTRANSFER  => true,
            CURLINFO_HEADER_OUT     => true
        );
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); /* Added to avoid "error :SSL certificate problem, verify that the CA cert is OK" */
        curl_setopt_array($curl, $options);
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, array(&$this, 'parseHeader')); /* add callback header function to process response headers */
        $response       = curl_exec($curl);
        $responsecode   = curl_getinfo($curl,CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($responsecode !== 200) {
            throw new Exception('Code réponse incorrect (api rk): '.$responsecode);
        }
        
        return $response;
    }
    
    
    /**
     *
     * @return type 
     */
    public function getJSONFitnessActivities($numpage = 0 , $pageSize = 25, $noEarlierThan = '1970-01-01', $noLaterThan = '2012-07-07', $modifiedNoEarlierThan = '1970-01-01', $modifiedNoLaterThan = '2012-07-07')
    {
        $userNode   = $this->requestHealthGraph('/user');
        $curNode    = $this->requestJSONHealthGraph($userNode->fitness_activities,$numpage , $pageSize , $noEarlierThan , $noLaterThan , $modifiedNoEarlierThan , $modifiedNoLaterThan);
        return $curNode;
        $activities = array();
        foreach ($curNode->items as $item) {
            $activity           = (array)$item;
            $activityDetails    = $this->requestHealthGraph($item->uri);
            $activities[]       = array_merge($activity, array('total_calories' => $activityDetails->total_calories));
            break;
        }
        
        return $activities;
    }
    
    
    /**
     *
     * @return type 
     */
    public function synchronizeActivitiesWihtUser($params)
    {
        $this->setAccessToken($params['access_token']);
        $activities = $this->getFitnessActivities();
        //$this->
    }
    
    /**
     *
     * @param type $curl
     * @param type $header
     * @return type 
     */
    private function parseHeader($curl,$header)
    {
        if (strstr($header,'Location: ')) {
            $this->requestRedirectUrl = substr($header, 10, strlen($header) - 12);
        }
        
        return strlen($header);
    }
    
}