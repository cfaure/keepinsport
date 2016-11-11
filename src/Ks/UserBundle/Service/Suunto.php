<?php

namespace Ks\UserBundle\Service;

use Symfony\Bundle\DoctrineBundle\Registry;
use Ks\UserBundle\User;

/**
 * Description of Suunto
 *
 * @author FMO
 */
class Suunto
    extends \Twig_Extension
{
    protected $_doctrine;
    protected $_appKey;
    protected $_accessToken;
    
    const API_BASE_URL = 'https://uiservices.movescount.com/';
    
    /**
     *
     * @param Registry $doctrine
     * @param type $appKey
     */
    public function __construct(Registry $doctrine, $appKey)
    {
        $this->_doctrine            = $doctrine;
        $this->_appKey              = $appKey;
        $this->_accessToken         = null;
        $this->_email               = null;
        $this->_startDate           = null;
    }
    
    // La méthode getName(), obligatoire
    public function getName()
    {
        return 'Suunto';
    }
	
    /**
     * Génération d'une clé par utilisateur de 64 caractères (obligatoire)
     * => Userkey should be generated and stored in partner sw. It should be unique for each user. Userkey must be exactly 64 random characters (must be URL compatible characters).
     * 
     * 
     * @param type 
     */
    public function getAccessToken()
    {
        $len = 64;
        $key = '';
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

        for ($i = 0; $i < $len; ++$i)
            $key .= substr($chars, (mt_rand() % strlen($chars)), 1);
        
        $this->_accessToken = $key;
        
        return $this->_accessToken;
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
     * Partner SW Movescount 1. Take app into use (members/private/applications)
     */
    public function setAppIntoUse($email, $startDate)
    {
        $url = self::API_BASE_URL."members/private/applications";
        
        $this->_email = $email;
        $this->_startDate = $startDate;
        
        $params = array(
            'appkey' => $this->_appKey,
            'userkey' => $this->_accessToken,
            'email' => $this->_email
        );
        
        $url .= '?'.http_build_query($params);
        //var_dump($url);
        
        $postFields = json_encode($params);
        
	$options 		= array(
		  CURLOPT_URL            	=> $url,
		  CURLOPT_RETURNTRANSFER 	=> true,       // Retourner le contenu téléchargé dans une chaine (au lieu de l'afficher directement)
		  CURLOPT_HEADER         	=> false,      // Ne pas inclure l'entête de réponse du serveur dans la chaine retournée
		  CURLOPT_HTTPHEADER		=> array("Content-type: application/json"),
                  CURLOPT_SSL_VERIFYPEER        => false,
		  //CURLOPT_FAILONERROR         => true,       // Gestion des codes d'erreur HTTP supérieurs ou égaux à 400
		  CURLOPT_POST                  => true,       // Effectuer une requête de type POST
		  CURLOPT_POSTFIELDS            => $postFields // Le tableau associatif contenant les variables envoyées par POST au serveur
	);
                
	//open connection
	$curl = curl_init();
    
	curl_setopt_array($curl, $options);
        
	$response 	= curl_exec($curl);
	$responsecode 	= curl_getinfo($curl, CURLINFO_HTTP_CODE);
	
	//var_dump($response, $responsecode);
        
	if (curl_errno($curl)) {
		// Le message d'erreur correspondant est affiché
		//var_dump($response, $responsecode, "ERREUR curl_exec : ".curl_error($curl));
                return $responsecode;
	}
        
	//close connection
	curl_close($curl);
        
        //var_dump("------$responsecode=".$responsecode);
        //var_dump("------$response=".$response);
        
        if ($responsecode === 200) {
            //FMO : demande KS envoyée à MOVESCOUNT en attente validation par le user
            $response       = htmlentities($response,ENT_NOQUOTES);
            $decoderesponse = json_decode($response);
            
            //return $decoderesponse;
        } elseif (in_array($responsecode, array('201'))) {
            //FMO : demande KS acceptée depuis MOVESCOUNT
            //$this->api_request_log[] = array('name' => $name, 'type' => $type, 'result' => $responsecoce, 'time' => microtime(true)-$orig);
            //return true;
        } elseif (in_array($responsecode, array('503'))) {
            //Erreur HTTP 503 Service unavailable (Service indisponible)
            //Cas d'une mise à jour sur movescount
            return $responsecode;
        } else {
            //FMO : erreur !
            // $this->api_last_error = "doRunkeeperRequest: request error => 'name' : ".$name.", 'type' : ".$type.", 'result' : ".$responsecode.", '".$name."' => ".$url;
            // $this->api_request_log[] = array('name' => $name, 'type' => $type, 'result' => 'error : '.$responsecode, 'time' => microtime(true)-$orig);
            return $responsecode;
        }
        return $responsecode;
    }
    
    /**
     *
     * @return type 
     */
    public function getActivities($maxcount =30)
    {
        $url = self::API_BASE_URL."moves/private";
        
        if ($this->_startDate == null) $startDate = date("Y-m-d H:i:s");//date('Y-m-01', strtotime("- 1 month"));
        
        $params = array(
            'appkey' => $this->_appKey,
            'email' => $this->_email,
            'userkey' => $this->_accessToken,
            'startdate' => $this->_startDate,
            'enddate' => new \DateTime('now'),
            'maxcount' => $maxcount
        );
        
        $url .= '?'.http_build_query($params);
        //var_dump($url);        exit;
        
        $options = array(
            CURLOPT_URL            	=> $url,
            CURLOPT_RETURNTRANSFER 	=> true,       // Retourner le contenu téléchargé dans une chaine (au lieu de l'afficher directement)
            CURLOPT_HEADER         	=> false,      // Ne pas inclure l'entête de réponse du serveur dans la chaine retournée
            CURLOPT_SSL_VERIFYPEER      => false,
            //CURLOPT_HTTPHEADER		=> array("Content-type: application/json"),
            CURLOPT_FAILONERROR         => true,       // Gestion des codes d'erreur HTTP supérieurs ou égaux à 400
            CURLOPT_POST                => false,       // Effectuer une requête de type GET
            CURLOPT_HTTPGET             => true,
            //CURLOPT_POSTFIELDS            => $postFields // Le tableau associatif contenant les variables envoyées par POST au serveur
	);
                
	//open connection
	$curl = curl_init();
    
	curl_setopt_array($curl, $options);
        
	$response 	= curl_exec($curl);
	$responsecode 	= curl_getinfo($curl, CURLINFO_HTTP_CODE);
	
	//var_dump($response, $responsecode);
        
	if (curl_errno($curl)) {
		// Le message d'erreur correspondant est affiché
		//var_dump("ERREUR curl_exec SUUNTO sync getActivities() : ".curl_error($curl));
                return $responsecode;
	}
        
	//close connection
	curl_close($curl);
        
        //var_dump("------$responsecode=".$responsecode);
        //var_dump("------$response=".$response);
        
        if ($responsecode === 200) {
            $response       = htmlentities($response,ENT_NOQUOTES);
            $decoderesponse = json_decode($response, true);
            
            return $decoderesponse;
        } elseif (in_array($responsecode, array('201','204','301','304'))) {
            //$this->api_request_log[] = array('name' => $name, 'type' => $type, 'result' => $responsecoce, 'time' => microtime(true)-$orig);
            return $responsecode;
        } else {
            //return $response;
            // $this->api_last_error = "doRunkeeperRequest: request error => 'name' : ".$name.", 'type' : ".$type.", 'result' : ".$responsecode.", '".$name."' => ".$url;
            // $this->api_request_log[] = array('name' => $name, 'type' => $type, 'result' => 'error : '.$responsecode, 'time' => microtime(true)-$orig);
            return $responsecode;
        }
    }
    
    /**
     *
     * @return type 
     */
    public function getActivity($MoveId)
    {
        $url = self::API_BASE_URL."moves/".$MoveId;
        
        $params = array(
            'appkey' => $this->_appKey,
            'email' => $this->_email,
            'userkey' => $this->_accessToken
        );
        
        $url .= '?'.http_build_query($params);
        //var_dump($url);
        
        $options = array(
            CURLOPT_URL            	=> $url,
            CURLOPT_RETURNTRANSFER 	=> true,       // Retourner le contenu téléchargé dans une chaine (au lieu de l'afficher directement)
            CURLOPT_HEADER         	=> false,      // Ne pas inclure l'entête de réponse du serveur dans la chaine retournée
            CURLOPT_SSL_VERIFYPEER      => false,
            //CURLOPT_HTTPHEADER		=> array("Content-type: application/json"),
            CURLOPT_FAILONERROR         => true,       // Gestion des codes d'erreur HTTP supérieurs ou égaux à 400
            CURLOPT_POST                => false,       // Effectuer une requête de type GET
            CURLOPT_HTTPGET             => true,
            //CURLOPT_POSTFIELDS            => $postFields // Le tableau associatif contenant les variables envoyées par POST au serveur
	);
                
	//open connection
	$curl = curl_init();
    
	curl_setopt_array($curl, $options);
        
	$response 	= curl_exec($curl);
	$responsecode 	= curl_getinfo($curl, CURLINFO_HTTP_CODE);
	
	//var_dump($response, $responsecode);
        
	if (curl_errno($curl)) {
		// Le message d'erreur correspondant est affiché
		var_dump("ERREUR curl_exec : ".curl_error($curl));
                return false;
	}
        
	//close connection
	curl_close($curl);
        
        //var_dump("------$responsecode=".$responsecode);
        //var_dump("------$response=".$response);
        
        if ($responsecode === 200) {
            $response       = htmlentities($response,ENT_NOQUOTES);
            $decoderesponse = json_decode($response, true);
            
            return $decoderesponse;
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
    public function getTrackPoints($MoveId)
    {
        $url = self::API_BASE_URL."moves/".$MoveId."/track?type=trackpoints&";
        
        $params = array(
            'appkey' => $this->_appKey,
            'email' => $this->_email,
            'userkey' => $this->_accessToken
        );
        
        $url .= http_build_query($params);
        //var_dump($url);
        
        $options = array(
            CURLOPT_URL            	=> $url,
            CURLOPT_RETURNTRANSFER 	=> true,       // Retourner le contenu téléchargé dans une chaine (au lieu de l'afficher directement)
            CURLOPT_HEADER         	=> false,      // Ne pas inclure l'entête de réponse du serveur dans la chaine retournée
            CURLOPT_SSL_VERIFYPEER      => false,
            //CURLOPT_HTTPHEADER		=> array("Content-type: application/json"),
            CURLOPT_FAILONERROR         => true,       // Gestion des codes d'erreur HTTP supérieurs ou égaux à 400
            CURLOPT_POST                => false,       // Effectuer une requête de type GET
            CURLOPT_HTTPGET             => true,
            //CURLOPT_POSTFIELDS            => $postFields // Le tableau associatif contenant les variables envoyées par POST au serveur
	);
                
	//open connection
	$curl = curl_init();
    
	curl_setopt_array($curl, $options);
        
	$response 	= curl_exec($curl);
	$responsecode 	= curl_getinfo($curl, CURLINFO_HTTP_CODE);
	
	//var_dump($response, $responsecode);
        
	if (curl_errno($curl)) {
		// Le message d'erreur correspondant est affiché
		var_dump("ERREUR curl_exec : ".curl_error($curl));
                return false;
	}
        
	//close connection
	curl_close($curl);
        
        //var_dump("------$responsecode=".$responsecode);
        //var_dump("------$response=".$response);
        
        if ($responsecode === 200) {
            $response       = htmlentities($response,ENT_NOQUOTES);
            $decoderesponse = json_decode($response, true);
            
            return $decoderesponse;
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
    public function getSamples($MoveId)
    {
        $url = self::API_BASE_URL."moves/".$MoveId."/samples?";
        
        $params = array(
            'appkey' => $this->_appKey,
            'email' => $this->_email,
            'userkey' => $this->_accessToken
        );
        
        $url .= http_build_query($params);
        //var_dump($url);
        
        $options = array(
            CURLOPT_URL            	=> $url,
            CURLOPT_RETURNTRANSFER 	=> true,       // Retourner le contenu téléchargé dans une chaine (au lieu de l'afficher directement)
            CURLOPT_HEADER         	=> false,      // Ne pas inclure l'entête de réponse du serveur dans la chaine retournée
            CURLOPT_SSL_VERIFYPEER      => false,
            //CURLOPT_HTTPHEADER		=> array("Content-type: application/json"),
            CURLOPT_FAILONERROR         => true,       // Gestion des codes d'erreur HTTP supérieurs ou égaux à 400
            CURLOPT_POST                => false,       // Effectuer une requête de type GET
            CURLOPT_HTTPGET             => true,
            //CURLOPT_POSTFIELDS            => $postFields // Le tableau associatif contenant les variables envoyées par POST au serveur
	);
                
	//open connection
	$curl = curl_init();
    
	curl_setopt_array($curl, $options);
        
        unset($response);
	$response 	= curl_exec($curl);
	$responsecode 	= curl_getinfo($curl, CURLINFO_HTTP_CODE);
	
	//var_dump($response, $responsecode);
        
	if (curl_errno($curl)) {
		// Le message d'erreur correspondant est affiché
		var_dump("ERREUR curl_exec : ".curl_error($curl));
                return false;
	}
        
	//close connection
	curl_close($curl);
        
        //var_dump("------$responsecode=".$responsecode);
        //var_dump("------$response=".$response);
        
        if ($responsecode === 200) {
            $response       = htmlentities($response,ENT_NOQUOTES);
            $decoderesponse = json_decode($response, true);
            
            return $decoderesponse;
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
    public function getMarks($MoveId)
    {
        $url = self::API_BASE_URL."moves/".$MoveId."/marks?";
        
        $params = array(
            'appkey' => $this->_appKey,
            'email' => $this->_email,
            'userkey' => $this->_accessToken
        );
        
        $url .= http_build_query($params);
        //var_dump($url);
        
        $options = array(
            CURLOPT_URL            	=> $url,
            CURLOPT_RETURNTRANSFER 	=> true,       // Retourner le contenu téléchargé dans une chaine (au lieu de l'afficher directement)
            CURLOPT_HEADER         	=> false,      // Ne pas inclure l'entête de réponse du serveur dans la chaine retournée
            CURLOPT_SSL_VERIFYPEER      => false,
            //CURLOPT_HTTPHEADER		=> array("Content-type: application/json"),
            CURLOPT_FAILONERROR         => true,       // Gestion des codes d'erreur HTTP supérieurs ou égaux à 400
            CURLOPT_POST                => false,       // Effectuer une requête de type GET
            CURLOPT_HTTPGET             => true,
            //CURLOPT_POSTFIELDS            => $postFields // Le tableau associatif contenant les variables envoyées par POST au serveur
	);
                
	//open connection
	$curl = curl_init();
    
	curl_setopt_array($curl, $options);
        
        unset($response);
	$response 	= curl_exec($curl);
	$responsecode 	= curl_getinfo($curl, CURLINFO_HTTP_CODE);
	
	//var_dump($response, $responsecode);
        
	if (curl_errno($curl)) {
		// Le message d'erreur correspondant est affiché
		var_dump("ERREUR curl_exec : ".curl_error($curl));
                return false;
	}
        
	//close connection
	curl_close($curl);
        
        //var_dump("------$responsecode=".$responsecode);
        //var_dump("------$response=".$response);
        
        if ($responsecode === 200) {
            $response       = htmlentities($response,ENT_NOQUOTES);
            $decoderesponse = json_decode($response, true);
            
            return $decoderesponse;
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
}