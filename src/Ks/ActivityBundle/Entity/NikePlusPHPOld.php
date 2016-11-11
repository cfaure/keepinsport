<?php
namespace Ks\ActivityBundle\Entity;

/**
 * v nikeplusphp.4.1
 * A PHP class that makes it easy to get your data from the Nike+ service 
 * 
 * NikePlusPHP v4.x requires PHP 5 with SimpleXML and cURL.
 * To get started you will need your Nike account information.
 * 
 * @author Charanjit Chana - http://charanj.it
 * @link http://nikeplusphp.org
 * @version 4.1
 * 
 * Usage:
 * $n = new NikePlusPHP('email@address.com', 'password');
 * $runs = $n->activities();
 * $run = $n->run('1234567890');
 */
 
class NikePlusPHPOld {

    /**
     * Public variables
     */
    public $loginCookies, $userId;
    
    /**
     * Private variables
     */
    private $cookie;

	/**
	 * __construct()
	 * Called when you initiate the class and keeps a cookie that allows you to keep authenticating
	 * against the Nike+ website.
	 * 
	 * @param string $username your Nike username, should be an email address
	 * @param string $password your Nike password 
	 */
	public function __construct($username, $password) {
        //($keepinsportUserId, $cookiesNikeDirAbsolute, $username = "", $password = "") {
        /*if( empty($username) && empty($password)) {
            $this->loginWithoutNikeId($keepinsportUserId, $cookiesNikeDirAbsolute);
        }
        else {*/
            $this->login($username, $password);
        //}
	}
	
	/**
	 * login()
	 * Called by __construct and performs the actual login action.
	 * 
	 * @param string $username
	 * @param string $password
	 * 
	 * @return string
	 */
	private function login($username, $password) {
		$url = 'https://secure-nikeplus.nike.com/nsl/services/user/login?app=b31990e7-8583-4251-808f-9dc67b40f5d2&format=json&contentType=plaintext';
		$loginDetails = 'app=b31990e7-8583-4251-808f-9dc67b40f5d2&format=json&contentType=plaintext&email='.urlencode($username).'&password='.$password;
        
        #--- CED ---#
        /*$path_cookie = $cookiesNikeDirAbsolute.$keepinsportUserId.'.txt';  
        if (!file_exists(realpath($path_cookie))) {
            touch($path_cookie); 
            $fic = fopen($path_cookie, 'a'); 
            fputs($fic, 'email='.urlencode($username).'&password='.$password);
        }*/

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //Set curl to return the data instead of printing it to the browser.
		curl_setopt($ch, CURLOPT_POSTFIELDS, $loginDetails);
		curl_setopt($ch, CURLOPT_URL, $url);
        
        #--- CED ---#
        //curl_setopt($ch, CURLOPT_COOKIEJAR, $path_cookie);
        //curl_setopt($ch, CURLOPT_COOKIEFILE, $path_cookie);  
        
		$data = curl_exec($ch);
		curl_close($ch);
		$noDoubleBreaks = str_replace(array("\n\r\n\r", "\r\n\r\n", "\n\n", "\r\r", "\n\n\n\n", "\r\r\r\r"), '||', $data);
		$sections = explode('||', $noDoubleBreaks);
		$headerSections = explode('Set-Cookie: ', $sections[0]);
		$body = $sections[1];
		for($i=1; $i<=count($headerSections); $i++) {
			$allheaders[] = @str_replace(array("\n\r", "\r\n", "\r", "\n\n", "\r\r"), "", $headerSections[$i]);
		}
		foreach($allheaders as $h) {
			$exploded[] = explode('; ', $h);
		}
		foreach($exploded as $e) {
			$string[] = $e[0];
		}
		$header = implode(';', $string);
		$this->cookie = $header;
        $this->loginCookies = json_decode($body);
        if ( isset($this->loginCookies->serviceResponse->body->User->screenName)) {
            $this->userId = $this->loginCookies->serviceResponse->body->User->screenName;
        }
	}
    
    private function loginWithoutNikeId($keepinsportUserId, $cookiesNikeDirAbsolute) {
		$url = 'https://secure-nikeplus.nike.com/nsl/services/user/login?app=b31990e7-8583-4251-808f-9dc67b40f5d2&format=json&contentType=plaintext';
        
        #--- CED ---#
        $infosNikeIdConnect = "";
        $path_cookie = $cookiesNikeDirAbsolute.$keepinsportUserId.'.txt';  
        if (file_exists(realpath($path_cookie))) {
            touch($path_cookie); 
            $fic = fopen($path_cookie, 'r'); 
            $infosNikeIdConnect = fgets($fic);
            fclose($fic);
        }
        
        $loginDetails = 'app=b31990e7-8583-4251-808f-9dc67b40f5d2&format=json&contentType=plaintext&'.$infosNikeIdConnect;

		$ch = curl_init();
        #--- CED ---#
        //curl_setopt($ch, CURLOPT_COOKIEJAR, $path_cookie);
        //curl_setopt($ch, CURLOPT_COOKIEFILE, $path_cookie);
        
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //Set curl to return the data instead of printing it to the browser.
		curl_setopt($ch, CURLOPT_POSTFIELDS, $loginDetails);
		curl_setopt($ch, CURLOPT_URL, $url);
        
        
        
		$data = curl_exec($ch);
		curl_close($ch);
		$noDoubleBreaks = str_replace(array("\n\r\n\r", "\r\n\r\n", "\n\n", "\r\r", "\n\n\n\n", "\r\r\r\r"), '||', $data);
		$sections = explode('||', $noDoubleBreaks);
		$headerSections = explode('Set-Cookie: ', $sections[0]);
		$body = $sections[1];
		for($i=1; $i<=count($headerSections); $i++) {
			$allheaders[] = @str_replace(array("\n\r", "\r\n", "\r", "\n\n", "\r\r"), "", $headerSections[$i]);
		}
		foreach($allheaders as $h) {
			$exploded[] = explode('; ', $h);
		}
		foreach($exploded as $e) {
			$string[] = $e[0];
		}
		$header = implode(';', $string);
		$this->cookie = $header;
        $this->loginCookies = json_decode($body);
        if ( isset($this->loginCookies->serviceResponse->body->User->screenName)) {
            $this->userId = $this->loginCookies->serviceResponse->body->User->screenName;
        }
	}

    /**
     * cookieValue()
     * returns the cookie value that has been set 
     */
    public function cookieValue() {
        return $this->cookie;
    }

    /**
     * getNikePlusFile()
     * collects the contents of the specified file from Nike+
     * 
     * @param string $path the file you wish to fetch
     */
    private function getNikePlusFile($path) {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
        curl_setopt($ch, CURLOPT_URL, $path);
        $data = curl_exec($ch);
        curl_close($ch);
        return json_decode($data);
    }
    
    /**
     * activities()
     * a list of your runs/activities
     *
     * @param boolean $allTime option - returns all time aggregate data if true, individual run data if false (default) 
     *
     * @return object
     */
    public function activities($allTime = false) {
        $loop = true;
        $start = 1;
        $increment = 30;
        $activities = new \stdClass;

        if(!$allTime) {
        	while($loop == true) {
	            $results = $this->getNikePlusFile('http://nikeplus.nike.com/plus/activity/running/'.$this->userId.'/lifetime/activities?indexStart='.$start.'&indexEnd='.($start+($increment - 1)));
            	//var_dump($results);
                if(!isset($results->activities)) {
	            	$loop = false;
            		break;
            	}
            	foreach($results->activities as $activity) {
	                $activities->activities[] = $activity->activity;
            	}
            	$start += $increment;
        	}
    	} else {
    		$activities = $this->getNikePlusFile('http://nikeplus.nike.com/plus/activity/running/'.$this->userId.'/lifetime/activities?indexStart=999999&indexEnd=1000000');
    	}
        return $activities;
    }
    
    public function allActivities($lastActivityId) {
        $loop = true;
        $activities = array();
        
        $activityId = $lastActivityId;
        //$activities->activities[] = $activityId;
        while($loop == true) {
            $activity = $this->run($activityId);
            
            if(isset($activity->activity)) {
                $activities[] = $activity->activity;
                //$nextId = ;
                if (isset($activity->activity->prevId) && $activity->activity->prevId != NULL) {
                    $activityId = $activity->activity->prevId;
                    //$activities->activities[] = $activityId;
                } else {
                    $loop = false;
                }
            } else {
                $loop = false;
            }
        }
        return $activities;
    }
    
    public function getLastActivities() {
        $loop = true;
        $start = 1;
        $increment = 30;
        $activities = new \stdClass;
        
        $totalRuns = $this->totalRuns();
        $runsNumberToRecup = $totalRuns > 5 ? 5 :$totalRuns;
        
        $runsRecup = 0;
        $i = 0;
        while($runsRecup < $runsNumberToRecup) {
            $results = $this->getNikePlusFile('http://nikeplus.nike.com/plus/activity/running/'.$this->userId.'/lifetime/activities?indexStart='.$i.'&i
            $results = ndexEnd='.($i));
            if(isset($results->activities)) {
                foreach($results->activities as $activity) {
	                $activities->activities[] = $this->run($activity->activity->activityId)->activity;
                    $runsRecup++;
            	}
            } else {
                $runsNumberToRecup -= 1;
            }
            $i += 1;
            //if( $i >= 7 ) break;
        }
        
        return $activities->activities;
    }
    
    
    public function test() {
        $loop = true;
        $start = 1;
        $increment = 1;
        $activities = new \stdClass;
        
        $totalRuns = $this->totalRuns();
        $runsNumberToRecup = $totalRuns > 5 ? 5 :$totalRuns;
        
        $runsRecup = 0;
        $i = 0;
        while($runsRecup < $runsNumberToRecup) {
            $results = $this->getNikePlusFile('http://nikeplus.nike.com/plus/activity/running/'.$this->userId.'/lifetime/activities?indexStart='.$i.'&indexEnd='.($i));
            if(isset($results->activities)) {
                foreach($results->activities as $activity) {
                    $activities->activities[] = $this->run($activity->activity->activityId)->activity;
                    $runsRecup++;
            	}
            } else {
                $runsNumberToRecup -= 1;
            }
            $i += 1;
 
        }
       
        //$results = $this->getNikePlusFile('http://nikeplus.nike.com/plus/activity/running/'.$this->userId.'/lifetime/activities?indexStart=0&indexEnd=10');
        
        return $results;
    }
    
    /**
     * run()
     * collects the data of a specific run
     * 
     * @param string $id the id of the run you wish to get the data for
     *
     * @return object
     */
    public function run($id) {
        return $this->getNikePlusFile('http://nikeplus.nike.com/plus/running/ajax/'.$id);
    }
    
    public function run2($id) {
        return $this->getNikePlusFile('http://nikeplus.nike.com/plus/activity/running/detail/'.$id);
    }
    
    
    /**
     * totalRuns()
     * return the total runs number
     * 
     *
     * @return integer
     */
    public function totalRuns() {
        $result = $this->getNikePlusFile('http://nikeplus.nike.com/plus/activity/running/'.$this->userId.'/lifetime/activities?indexStart=999999&indexEnd=1000000');
        $totalRuns = $result->timeSpanMetrics->metrics->totalRuns;
        
        return $totalRuns;
    }
    
    /**
     * lastActivity()
     * return the last activity
     * 
     *
     * @return objet
     */
    public function lastActivityId() {
        $result = $this->getNikePlusFile('http://nikeplus.nike.com/plus/activity/running/'.$this->userId.'/lifetime/activities?indexStart=0&indexEnd=0');
        //return $result;
        $dayRunsNumber = count($result->activities);
        return $result->activities[$dayRunsNumber - 1]->activity->activityId;
    }
    
    /**
     * toMiles()
     * Convert a value from Km in to miles
     * 
     * @param float|string $distance
     * 
     * @return int
     */
    public function toMiles($distance) {
        return number_format(((float) $distance * 0.6213727366498068), 2, '.', ',');
    }
}