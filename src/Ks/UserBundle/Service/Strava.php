<?php

namespace Ks\UserBundle\Service;

use Doctrine\Tests\DBAL\Types\DateTest;
use Symfony\Bundle\DoctrineBundle\Registry;
use Ks\UserBundle\User;

/**
 * Description of Runkeeper
 *
 * @author Clem
 */
class Strava
    extends \Twig_Extension
{
    protected $_doctrine;
    protected $_clientId;
    protected $_clientSecret;
    protected $_authUrl;
    protected $_accessTokenUrl;
    protected $_accessToken;
    
    protected $lastRequest;
    protected $lastRequestData;
    protected $lastRequestInfo;
    
    const API_BASE_URL = 'https://www.strava.com/api/v3';
    
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
        return 'Strava';
    }
	
    // La méthode getFunctions(), qui retourne un tableau avec les fonctions qui peuvent être appelées depuis cette extension
    public function getFunctions()
    {
        return array(
            'stravaRegisterUrl' => new \Twig_Function_Method($this, 'registerUrl') 
        );
    }
    
    /**
     * Sends GET request to specified API endpoint
     * @param string $request
     * @param string $accessToken
     * @param array $parameters
     * @example http://strava.github.io/api/v3/athlete/#koms
     * @return function
     */
    public function get($request, $parameters = array())
    {
        $requestUrl = $this->parseGet(self::API_BASE_URL.$request, $parameters);

        return $this->request($requestUrl);
    }

    /**
     * Appends query array onto URL
     * @param string $url
     * @param array $query
     * @return string
     */
    protected function parseGet($url, $query)
    {
        $append = strpos($url, '?') === false ? '?' : '&';

        return $url.$append.http_build_query($query);
    }
    
    /**
     * Makes HTTP Request to the API
     * @param string $url
     * @param array $parameters
     * @return mixed
     */
    protected function request($url, $parameters = array())
    {
        $this->lastRequest      = $url;
        $this->lastRequestData  = $parameters;
        $curl                   = curl_init($url);
        $curlOptions = array(
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$this->_accessToken
            ),
            CURLOPT_SSL_VERIFYPEER  => false,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_REFERER         => $url,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_POST            => false
        );

        curl_setopt_array($curl, $curlOptions);
        $response               = curl_exec($curl);
        $error                  = curl_error($curl);
        $this->lastRequestInfo  = curl_getinfo($curl);
        curl_close($curl);

        if (!$response) {
            return $error;
        } else {
            return $this->parseResponse($response);
        }
    }

    /**
     * Parses JSON as PHP object
     * @param string $response
     * @return object
     */
    protected function parseResponse($response)
    {
        return json_decode($response);
    }
    
    /**
     * Given a valid authorisation code, get an access token that will
     * link to user strava's profile.
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
            'code'          => $authCode,
            'client_id'     => $this->_clientId,
            'client_secret' => $this->_clientSecret
        ));
        curl_setopt_array($curl, array(
            CURLOPT_URL             => $this->_accessTokenUrl,
            CURLOPT_POST            => true,
            CURLOPT_POSTFIELDS      => $params,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_SSL_VERIFYPEER  => false
        ));
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
        return 'https://www.strava.com/oauth/authorize?'
            .'client_id='.$this->_clientId
            .'&response_type=code'
            .'&redirect_uri='.$callbackUri
            .'&scope=write'
            .'&state=mystate'
            .'&approval_prompt=force';
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
     * @param string $after
     * @return array
     */
    public function getFitnessActivities($after = '1970-01-01 00:00:00')
    {
        // il faut convertir le datetime classique au format T/Z GMT de Strava
        // il doit y avoir plus simple... mais ça marche
        list($afterDate, $afterTime)    = explode(" ", $after);
        list($y, $m, $d)                = explode("-", $afterDate);
        list($h, $i, $s)                = explode(":", $afterTime);

        $gmAfterDate                    = gmdate("Y-m-d\TH:i:s\Z", mktime($h, $i, $s, $m, $d, $y));
        $afterDateTime                  = new \DateTime($gmAfterDate);

        $activities = $this->get(
            '/athlete/activities',
            array('after' => $afterDateTime->getTimestamp())
        );
        
        return $activities;
    }

    /**
     *
     * @return type 
     */
    public function synchronizeActivitiesWihtUser($token)
    {
        $this->setAccessToken($token);
        $activities = $this->getFitnessActivities(date('Y-m-d'));
        //$this->
    }
    
    /**
     * Decodes a polyline that was encoded using the Google Maps method.
     *
     * The encoding algorithm is detailed here:
     * http://code.google.com/apis/maps/documentation/polylinealgorithm.html
     *
     * This function is based off of Mark McClure's JavaScript polyline decoder
     * (http://facstaff.unca.edu/mcmcclur/GoogleMaps/EncodePolyline/decode.js)
     * which was in turn based off Google's own implementation.
     *
     * This function assumes a validly encoded polyline.  The behaviour of this
     * function is not specified when an invalid expression is supplied.
     *
     * @param String $encoded the encoded polyline.
     * @return Array an Nx2 array with the first element of each entry containing
     *  the latitude and the second containing the longitude of the
     *  corresponding point.
     */
    public function decodePolylineToArray($encoded)
    {
      $length   = strlen($encoded);
      $index    = 0;
      $points   = array();
      $lat      = 0;
      $lng      = 0;

      while ($index < $length) {
        // Temporary variable to hold each ASCII byte.
        $b = 0;

        // The encoded polyline consists of a latitude value followed by a
        // longitude value.  They should always come in pairs.  Read the
        // latitude value first.
        $shift  = 0;
        $result = 0;
        do {
          // The `ord(substr($encoded, $index++))` statement returns the ASCII
          //  code for the character at $index.  Subtract 63 to get the original
          // value. (63 was added to ensure proper ASCII characters are displayed
          // in the encoded polyline string, which is `human` readable)
          $b = ord(substr($encoded, $index++)) - 63;

          // AND the bits of the byte with 0x1f to get the original 5-bit `chunk.
          // Then left shift the bits by the required amount, which increases
          // by 5 bits each time.
          // OR the value into $results, which sums up the individual 5-bit chunks
          // into the original value.  Since the 5-bit chunks were reversed in
          // order during encoding, reading them in this way ensures proper
          // summation.
          $result |= ($b & 0x1f) << $shift;
          $shift += 5;
        } while ($b >= 0x20);
        // Continue while the read byte is >= 0x20 since the last `chunk`
        // was not OR'd with 0x20 during the conversion process. (Signals the end)
        

        // Check if negative, and convert. (All negative values have the last bit
        // set)
        $dlat = (($result & 1) ? ~($result >> 1) : ($result >> 1));

        // Compute actual latitude since value is offset from previous value.
        $lat += $dlat;

        // The next values will correspond to the longitude for this point.
        $shift = 0;
        $result = 0;
        do {
          $b = ord(substr($encoded, $index++)) - 63;
          $result |= ($b & 0x1f) << $shift;
          $shift += 5;
        } while ($b >= 0x20);

        $dlng = (($result & 1) ? ~($result >> 1) : ($result >> 1));
        $lng += $dlng;

        // The actual latitude and longitude values were multiplied by
        // 1e5 before encoding so that they could be converted to a 32-bit
        // integer representation. (With a decimal accuracy of 5 places)
        // Convert back to original values.
        $points[] = array('lat' => $lat * 1e-5, 'lon' => $lng * 1e-5);
      }

      return $points;
    }
}