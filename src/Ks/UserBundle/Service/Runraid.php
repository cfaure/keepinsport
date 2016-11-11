<?php

namespace Ks\UserBundle\Service;

use Symfony\Bundle\DoctrineBundle\Registry;
use Ks\UserBundle\User;

/**
 * Description of RUNRAID
 *
 * @author FMO
 */
class Runraid
    extends \Twig_Extension
{
    protected $_doctrine;
    protected $_appKey;
    protected $_accessToken;
    
    const API_BASE_URL = 'http://runraid.free.fr/';
    
    /**
     *
     * @param Registry $doctrine
     * @param type $appKey
     */
    public function __construct(Registry $doctrine)
    {
        $this->_doctrine            = $doctrine;
        $this->_startDate           = null;
    }
    
    // La méthode getName(), obligatoire
    public function getName()
    {
        return 'Runraid';
    }
	
    /**
     * Get data from runraid
     */
    public function getCalendrier($startDate=null)
    {
        $url = self::API_BASE_URL."json.php";
        
        $this->_startDate = $startDate;
        
        $params = array(
            'query' => "getCalendrier"
        );
        
        $url .= '?'.http_build_query($params);
        //var_dump($url);
        
        //$postFields = json_encode($params);
        
	$options 		= array(
		  CURLOPT_URL            	=> $url,
		  CURLOPT_RETURNTRANSFER 	=> true,       // Retourner le contenu téléchargé dans une chaine (au lieu de l'afficher directement)
		  CURLOPT_HEADER         	=> false,      // Ne pas inclure l'entête de réponse du serveur dans la chaine retournée
		  CURLOPT_HTTPHEADER		=> array("Content-type: application/json"),
                  CURLOPT_SSL_VERIFYPEER        => false,
		  //CURLOPT_FAILONERROR         => true,       // Gestion des codes d'erreur HTTP supérieurs ou égaux à 400
		  //CURLOPT_POST                  => false,       // Effectuer une requête de type POST
                  CURLOPT_CUSTOMREQUEST         => 'GET',
		  //CURLOPT_POSTFIELDS            => $postFields // Le tableau associatif contenant les variables envoyées par POST au serveur
	);
                
	//open connection
	$curl = curl_init();
    
	curl_setopt_array($curl, $options);
        
	$response 	= curl_exec($curl);
	$responsecode 	= curl_getinfo($curl, CURLINFO_HTTP_CODE);
	
	//var_dump("getCalendrier: ".$url, $response, $responsecode, curl_errno($curl), curl_error($curl));exit;
        
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
            $start = stripos($response, "<body");
            $end = stripos($response, "</body");

            $body = substr($response,$start+8, $end-$start);
            $decoderesponse = mb_convert_encoding($body,'UTF-8','UTF-8'); 
            $result = json_decode($decoderesponse);
            switch (json_last_error()) {
                case JSON_ERROR_NONE:
                    echo ' - Aucune erreur';
                break;
                case JSON_ERROR_DEPTH:
                    echo ' - Profondeur maximale atteinte';
                break;
                case JSON_ERROR_STATE_MISMATCH:
                    echo ' - Inadéquation des modes ou underflow';
                break;
                case JSON_ERROR_CTRL_CHAR:
                    echo ' - Erreur lors du contrôle des caractères';
                break;
                case JSON_ERROR_SYNTAX:
                    echo ' - Erreur de syntaxe ; JSON malformé';
                break;
                case JSON_ERROR_UTF8:
                    echo ' - Caractères UTF-8 malformés, probablement une erreur d\'encodage';
                break;
                default:
                    echo ' - Erreur inconnue';
                break;
            }

            //echo PHP_EOL;
            
            return $decoderesponse;
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
     * Get data from runraid
     */
    public function getCalendrierFromId($id) {
        $url = self::API_BASE_URL."json.php";
        
        $this->_startDate = $startDate;
        
        $params = array(
            'query' =>  "getCalendrierFromId",
            'id'    =>  $id
        );
        
        $url .= '?'.http_build_query($params);
        //var_dump($url);
        
        //$postFields = json_encode($params);
        
	$options 		= array(
            CURLOPT_URL            	=> $url,
            CURLOPT_RETURNTRANSFER 	=> true,       // Retourner le contenu téléchargé dans une chaine (au lieu de l'afficher directement)
            CURLOPT_HEADER         	=> false,      // Ne pas inclure l'entête de réponse du serveur dans la chaine retournée
            CURLOPT_HTTPHEADER		=> array("Content-type: application/json"),
            CURLOPT_SSL_VERIFYPEER        => false,
            //CURLOPT_FAILONERROR         => true,       // Gestion des codes d'erreur HTTP supérieurs ou égaux à 400
            //CURLOPT_POST                  => false,       // Effectuer une requête de type POST
            CURLOPT_CUSTOMREQUEST         => 'GET',
            //CURLOPT_POSTFIELDS            => $postFields // Le tableau associatif contenant les variables envoyées par POST au serveur
	);
                
	//open connection
	$curl = curl_init();
    
	curl_setopt_array($curl, $options);
        
	$response 	= curl_exec($curl);
	$responsecode 	= curl_getinfo($curl, CURLINFO_HTTP_CODE);
	
	//var_dump("getCalendrier: ".$url, $response, $responsecode, curl_errno($curl), curl_error($curl));exit;
        
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
            //$start = stripos($response, "<body");
            $start = stripos($response, "<body");
            $end = stripos($response, "</body");
            $bug = stripos($response, "Resource id #2");
            /*
            var_dump("bug=".$bug);
            var_dump("start=".$start);
            var_dump("end=".$end);
            */
            //if ($end =="") var_dump("ici");
            if ($bug =="" && $end !="") $body = substr($response,$start+8, $end-$start-9);
            else $body = substr($response,$start+8, $bug-$start-9);
            
            //echo "body=".$body;
            //echo "END";
            
            $decodeBody = str_replace( "<br>", '', $decodeBody); //Nécessaire malheureusement car doublé coté runraid...
            $decodeBody = (string)utf8_encode($body);
            $decodeBody = str_replace( "\n", '', $decodeBody);
            $decodeBody = str_replace( "\r", '', $decodeBody);
            $decodeBody = str_replace( "\t", '', $decodeBody);
            $decodeBody = str_replace( "\000", '', $decodeBody);
            $decodeBody = str_replace( "\001", '', $decodeBody);
            $decodeBody = str_replace( "\\", '', $decodeBody);

            /*
            ini_set('xdebug.var_display_max_depth', 5);
            ini_set('xdebug.var_display_max_children', 256);
            ini_set('xdebug.var_display_max_data', 10000);
            var_dump($decodeBody);
            */
            $response = json_decode($decodeBody, true);
            switch (json_last_error()) {
                case JSON_ERROR_NONE:
                    //echo ' - Aucune erreur';
                break;
                case JSON_ERROR_DEPTH:
                    echo ' - ERREUR : Profondeur maximale atteinte';
                break;
                case JSON_ERROR_STATE_MISMATCH:
                    echo ' - ERREUR : Inadéquation des modes ou underflow';
                break;
                case JSON_ERROR_CTRL_CHAR:
                    echo ' - ERREUR : Erreur lors du contrôle des caractères';
                break;
                case JSON_ERROR_SYNTAX:
                    echo ' - ERREUR : Erreur de syntaxe ; JSON malformé';
                break;
                case JSON_ERROR_UTF8:
                    echo ' - ERREUR : Caractères UTF-8 malformés, probablement une erreur d\'encodage';
                break;
                default:
                    echo ' - ERREUR : Erreur inconnue';
                break;
            }
            return $response;
            
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
     * Get gpx from openrunner
     */
    public function getGPXFromOpenrunner($id)
    {
        $url = "http://export.openrunner.com/kml/exportImportGPX.php";
        
        $params = array(
            'rttype'    => 0,
            'id'        => $id
        );
        
        $url .= '?'.http_build_query($params);
        //var_dump($url);
        
        //$postFields = json_encode($params);
        
	$options 		= array(
            CURLOPT_URL            	=> $url,
            CURLOPT_RETURNTRANSFER 	=> true,       // Retourner le contenu téléchargé dans une chaine (au lieu de l'afficher directement)
            CURLOPT_HEADER         	=> false,      // Ne pas inclure l'entête de réponse du serveur dans la chaine retournée
            CURLOPT_HTTPHEADER		=> array("Content-type: application/json"),
            CURLOPT_SSL_VERIFYPEER        => false,
            //CURLOPT_FAILONERROR         => true,       // Gestion des codes d'erreur HTTP supérieurs ou égaux à 400
            //CURLOPT_POST                  => false,       // Effectuer une requête de type POST
            CURLOPT_CUSTOMREQUEST         => 'GET',
            //CURLOPT_POSTFIELDS            => $postFields // Le tableau associatif contenant les variables envoyées par POST au serveur
	);
                
	//open connection
	$curl = curl_init();
    
	curl_setopt_array($curl, $options);
        
	$response 	= curl_exec($curl);
	$responsecode 	= curl_getinfo($curl, CURLINFO_HTTP_CODE);
	
	//var_dump("getGPXFromOpenrunner: ".$url, $response, $responsecode, curl_errno($curl), curl_error($curl));exit;
        
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
            return $response;
            
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
}