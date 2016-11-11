<?php

namespace Ks\UserBundle\Service;

use Symfony\Bundle\DoctrineBundle\Registry;
use Ks\UserBundle\User;


//To configure the API : code.google.com/apis/console/
// http://www.w3resource.com/API/google-plus/tutorial.php


/**
 * Description of Runkeeper
 * 
 * @author Clem
 */
class GoogleContact
    extends \Twig_Extension
{
    protected $_doctrine;
    protected $_clientId;
    protected $_clientSecret;
    protected $_accessTokenUrl;
    protected $_accessToken;
    public    $_accessContactUrl;
    public    $_urlSchema;
    
    
    /**
     *
     * @param Registry $doctrine
     * @param type $clientId
     * @param type $clientSecret
     * @param type $authUrl
     * @param type $accessTokenUrl 
     */
    public function __construct(Registry $doctrine, $clientId, $clientSecret, $accessTokenUrl, $accessContactUrl, $urlSchema)
    {
        $this->_doctrine            = $doctrine;
        $this->_clientId            = $clientId;
        $this->_clientSecret        = $clientSecret;
        $this->_accessTokenUrl      = $accessTokenUrl;
        $this->_accessToken         = null;
        $this->_accessContactUrl    = $accessContactUrl;
        $this->_urlSchema           = $urlSchema;
    }
    
    // La méthode getName(), obligatoire
    public function getName()
    {
        return 'GoogleContact';
    }
	
    // La méthode getFunctions(), qui retourne un tableau avec les fonctions qui peuvent être appelées depuis cette extension
    public function getFunctions()
    {
        return array(
            'GoogleContactRegisterUrl' => new \Twig_Function_Method($this, 'registerUrl') 
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
        
        
        $ch   = curl_init();

        $fields= array(
            'code'          =>urlencode($authCode),
            'client_id'     =>urlencode($this->_clientId),
            'client_secret' =>urlencode($this->_clientSecret),
            'redirect_uri'  =>urlencode($redirectUri),
            'grant_type'    =>urlencode('authorization_code'),
        );
        
        $fields_string='';

        foreach($fields as $key=> $value) { $fields_string .= $key.'='.$value.'&'; }
       
        $fields_string=rtrim($fields_string,'&');
        
        curl_setopt($ch,CURLOPT_URL,$this->_accessTokenUrl);
        curl_setopt($ch,CURLOPT_POST,5);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);

        $decoderesponse = json_decode($result);

        if ( ($decoderesponse == null) || isset($decoderesponse->error) ) {
            $error = $decoderesponse->error != '' ? $decoderesponse->error : 'Réponse vide';
            throw new \Exception($error);
        }
        
        if (isset($decoderesponse->access_token) && $decoderesponse->access_token != '') {
            $this->_accessToken = $decoderesponse->access_token;
        } else {
            throw new \Exception('Réponse ok mais Access Token vide');
        }
        
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
    
   
    
}
