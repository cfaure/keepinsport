<?php

namespace Ks\UserBundle\Service;

use Symfony\Bundle\DoctrineBundle\Registry;
use Ks\UserBundle\User;

/**
 * Description of Polar
 *
 * @author FMO
 */
class Polar
    extends \Twig_Extension
{
    protected $_doctrine;
    const API_BASE_URL = 'https://www.polarpersonaltrainer.com/';
    
    /**
     *
     * @param Registry $doctrine
     * @param type $appKey
     */
    public function __construct(Registry $doctrine)
    {
        $this->_doctrine    = $doctrine;
        $this->_username    = null;
        $this->_password    = null;
        $this->_identifier  = null;
    }
    
    // La méthode getName(), obligatoire
    public function getName()
    {
        return 'Polar';
    }
	
    
    /**
     */
    public function authorize($identifier, $strUsername, $strPassword)
    {
        $url = self::API_BASE_URL."index.ftl";
        
        $this->_username = $strUsername;
        $this->_password = $strPassword;
        $this->_identifier = $identifier;
        
        $params = array(
            "email"     => $strUsername,
            "password"  => $strPassword,
            ".action"   => "login",
            "tz"        => "0"
        );
        
        $url .= '?'.http_build_query($params);
        
        $options = array(
            CURLOPT_FRESH_CONNECT => TRUE,
            CURLOPT_URL => $url,
            CURLOPT_FOLLOWLOCATION => TRUE,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true, // Retourner le contenu téléchargé dans une chaine (au lieu de l'afficher directement)
            CURLOPT_HEADER => false, // Ne pas inclure l'entête de réponse du serveur dans la chaine retournée
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_COOKIESESSION => FALSE,
            CURLOPT_AUTOREFERER => TRUE,
            CURLOPT_VERBOSE => FALSE,
            CURLOPT_COOKIEJAR => $this->_identifier,
            CURLOPT_COOKIEFILE => $this->_identifier
        );
                
	//open connection
	$curl = curl_init();
    
	curl_setopt_array($curl, $options);
        
	$response 	= curl_exec($curl);
	$responsecode 	= curl_getinfo($curl, CURLINFO_HTTP_CODE);
	
	//var_dump("authorize: ".$url, $response, $responsecode, curl_errno($curl), curl_error($curl));exit;
        
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
    public function getActivities($strUsername, $strPassword, $startDate)
    {
        $url = self::API_BASE_URL."user/calendar/inc/listview.ftl";
        
        $end_date = date('d.m.Y');
        
        $start_date = $startDate;
        $arrParams = array(
            "startDate" => $start_date,
            "endDate"   => $end_date,
        );
        $url .= '?'.http_build_query($arrParams);
        //var_dump($url);
        
        $options = array(
            CURLOPT_FRESH_CONNECT => TRUE,
            CURLOPT_URL => $url,
            CURLOPT_FOLLOWLOCATION => TRUE,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true, // Retourner le contenu téléchargé dans une chaine (au lieu de l'afficher directement)
            CURLOPT_HEADER => false, // Ne pas inclure l'entête de réponse du serveur dans la chaine retournée
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_COOKIESESSION => FALSE,
            CURLOPT_AUTOREFERER => TRUE,
            CURLOPT_VERBOSE => FALSE,
            CURLOPT_COOKIEJAR => $this->_identifier,
            CURLOPT_COOKIEFILE => $this->_identifier
        );
                
	//open connection
	$curl = curl_init();
        
        curl_setopt_array($curl, $options);
        
	$response 	= curl_exec($curl);
	$responsecode 	= curl_getinfo($curl, CURLINFO_HTTP_CODE);
	
	//var_dump("getActivities: ".$url, $responsecode, curl_errno($curl), curl_error($curl));
        
	if (curl_errno($curl)) {
		// Le message d'erreur correspondant est affiché
		//var_dump("ERREUR curl_exec Polar sync getActivities() : ".curl_error($curl));
                return $responsecode;
	}
        
	//close connection
	curl_close($curl);
        
        //var_dump("------$responsecode=".$responsecode);
        //var_dump("------$response=".$response);
        
        if ($responsecode === 200) {
            \libxml_use_internal_errors(true);
            $dom = new \DOMDocument();
            $dom->loadHTML($response);
            //var_dump($response);
            $dom->preserveWhiteSpace = false;
            $calItems = $dom->getElementById('calItems');

            $activities = array();
            if (is_null($calItems)) {
                //<p class="listView">No diary items in the selected time frame.</p>
            }
            else {
                $xpath = new \DOMXPath($dom);
                $values=$xpath->query('//input[@name="calendarItem"]');
                //var_dump($values);exit;
                foreach($values as $value) {
                    $activities[]=$value->getAttribute('value');
                }
            }
            
            return $activities;
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
    public function getActivity($polarActivityId)
    {
        $url = self::API_BASE_URL."user/calendar/item/multisportExercise.xml?id=".$polarActivityId;
        
        $options = array(
            CURLOPT_FRESH_CONNECT => TRUE,
            CURLOPT_URL => $url,
            CURLOPT_FOLLOWLOCATION => TRUE,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true, // Retourner le contenu téléchargé dans une chaine (au lieu de l'afficher directement)
            CURLOPT_HEADER => false, // Ne pas inclure l'entête de réponse du serveur dans la chaine retournée
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_COOKIESESSION => FALSE,
            CURLOPT_AUTOREFERER => TRUE,
            CURLOPT_VERBOSE => FALSE,
            CURLOPT_COOKIEJAR => $this->_identifier,
            CURLOPT_COOKIEFILE => $this->_identifier
	);
                
	//open connection
	$curl = curl_init();
    
	curl_setopt_array($curl, $options);
        
	$response 	= curl_exec($curl);
	$responsecode 	= curl_getinfo($curl, CURLINFO_HTTP_CODE);
	
	//var_dump("getActivity: ".$url, $response, $responsecode, curl_errno($curl), curl_error($curl));
        
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
            $activity = array();
            
            \libxml_use_internal_errors(true);
            $xml = simplexml_load_string($response);
            //var_dump($xml);
            $durationData   = $xml->xpath("//object[@name='result']/prop[@name='duration']");
            $distanceData   = $xml->xpath("//object[@name='result']/prop[@name='distance']");
            //$caloriesData   = $xml->xpath("//object[@name='result']/prop[@name='calories']");
            $issuedAtData   = $xml->xpath("//object[@id='".$polarActivityId."']/prop[@name='websyncId']");
            $sportData      = $xml->xpath("//object[@name='sport']/prop[@name='name']");
            $ascentData     = $xml->xpath("//object[@name='altitudeInfo']/prop[@name='ascent']");
            $descentData    = $xml->xpath("//object[@name='altitudeInfo']/prop[@name='descent']");
            
            //var_dump($mainData[0]);//->xpath("//prop[@type='duration']"));
            $activity['Duration']           = (string)substr($durationData[0], 0, 7);
            $activity['Distance']           = (float)$distanceData[0];
            $activity['LocalStartTime']     = (string)$issuedAtData[0];
            $activity['Sport']              = (string)$sportData[0];
            $activity['AscentAltitude']     = isset($ascentData[0]) ? round((float)$ascentData[0]) : "-";
            $activity['DescentAltitude']    = isset($descentData[0]) ? round((float)$descentData[0]) : "-";
            
            return $activity;
            
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
    public function getTrackPoints($polarActivityId) {
        $url = self::API_BASE_URL."user/calendar/item/multisportExercise_ajaxAccessories.xml?id=".$polarActivityId;
        
        $options = array(
            CURLOPT_FRESH_CONNECT => TRUE,
            CURLOPT_URL => $url,
            CURLOPT_FOLLOWLOCATION => TRUE,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true, // Retourner le contenu téléchargé dans une chaine (au lieu de l'afficher directement)
            CURLOPT_HEADER => false, // Ne pas inclure l'entête de réponse du serveur dans la chaine retournée
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_COOKIESESSION => FALSE,
            CURLOPT_AUTOREFERER => TRUE,
            CURLOPT_VERBOSE => FALSE,
            CURLOPT_COOKIEJAR => $this->_identifier,
            CURLOPT_COOKIEFILE => $this->_identifier
	);
                
	//open connection
	$curl = curl_init();
    
	curl_setopt_array($curl, $options);
        
	$response 	= curl_exec($curl);
	$responsecode 	= curl_getinfo($curl, CURLINFO_HTTP_CODE);
	
	//var_dump("getTrackingPoint: ".$url, $response, $responsecode, curl_errno($curl), curl_error($curl));        exit;
	if (curl_errno($curl)) {
		// Le message d'erreur correspondant est affiché
		var_dump("ERREUR curl_exec : ".curl_error($curl));
                return false;
	}
        
	//close connection
	curl_close($curl);

        if ($responsecode === 200) {
            $trackingPoints = array();
            
            \libxml_use_internal_errors(true);
            $xml = simplexml_load_string($response);
            //var_dump($xml);
            //$trackingPointsData     = $xml->xpath("//prop[@name='route']/text()");
            $watchData              = $xml->xpath("//prop[@name='wUnitName']");
            
            //var_dump("ici:".json_encode$trackingPointsData[0][0][0]->gpx);exit;
            
            //$trackingPoints['waypoints']    = isset($trackingPointsData[0][0][0]) ? json_decode($trackingPointsData[0][0][0])->gpx : null;
            $trackingPoints['watch']        = isset($watchData[0]) ? (string)$watchData[0] : "-";
            
            return $trackingPoints;
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
    public function getGPX($polarActivityId) {
        $url = self::API_BASE_URL."user/calendar/index.gpx";
        
        $arrParams = array(
            ".action"           => "gpx",
            "items.0.item"      => $polarActivityId,
            "items.0.itemType"  => "OptimizedExercise",
         );
        
        $options = array(
            CURLOPT_FRESH_CONNECT => TRUE,
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded'),
            CURLOPT_FOLLOWLOCATION => FALSE,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPGET		=> 0,
            CURLOPT_POST		=> 1,
            CURLOPT_POSTFIELDS => http_build_query($arrParams),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true, // Retourner le contenu téléchargé dans une chaine (au lieu de l'afficher directement)
            CURLOPT_HEADER => false, 
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_COOKIESESSION => FALSE,
            CURLOPT_AUTOREFERER => TRUE,
            CURLOPT_VERBOSE => FALSE,
            CURLOPT_COOKIEJAR => $this->_identifier,
            CURLOPT_COOKIEFILE => $this->_identifier
	);
                
	//open connection
	$curl = curl_init();
    
	curl_setopt_array($curl, $options);
        
	$response 	= curl_exec($curl);
	$responsecode 	= curl_getinfo($curl, CURLINFO_HTTP_CODE);
	
	//var_dump("getGPX: ".$url, $response, $responsecode, curl_errno($curl), curl_error($curl));
        
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
}