<?php
namespace Ks\ActivityBundle\Service;

/**
 * Description of FfaService
 *
 * @author Clem
 */
class FfaService
{
    /**
     * Remplace les charactères accentués par leur
     * équivalent non accentué.
     * 
     * @param string $texte
     * @return string Chaine non accentuée
     */
    protected final function stripAccents($texte)
    {
		$texte = str_replace(
			array(
				'à', 'â', 'ä', 'á', 'ã', 'å',
				'î', 'ï', 'ì', 'í', 
				'ô', 'ö', 'ò', 'ó', 'õ', 'ø', 
				'ù', 'û', 'ü', 'ú', 
				'é', 'è', 'ê', 'ë', 
				'ç', 'ÿ', 'ñ',
				'À', 'Â', 'Ä', 'Á', 'Ã', 'Å',
				'Î', 'Ï', 'Ì', 'Í', 
				'Ô', 'Ö', 'Ò', 'Ó', 'Õ', 'Ø', 
				'Ù', 'Û', 'Ü', 'Ú', 
				'É', 'È', 'Ê', 'Ë', 
				'Ç', 'Ÿ', 'Ñ'
			),
			array(
				'a', 'a', 'a', 'a', 'a', 'a', 
				'i', 'i', 'i', 'i', 
				'o', 'o', 'o', 'o', 'o', 'o', 
				'u', 'u', 'u', 'u', 
				'e', 'e', 'e', 'e', 
				'c', 'y', 'n', 
				'A', 'A', 'A', 'A', 'A', 'A', 
				'I', 'I', 'I', 'I', 
				'O', 'O', 'O', 'O', 'O', 'O', 
				'U', 'U', 'U', 'U', 
				'E', 'E', 'E', 'E', 
				'C', 'Y', 'N'
			), $texte);
        
		return $texte;
	}
    
    /**
     *
     * @param type $str
     * @return type 
     */
    protected final function getDurationFromString($str)
    {            
        $matches        = array();
        $duration       = 0;
        
        if (preg_match('/(\d+)[^0-9]+(\d+)[^0-9]+(\d+)/', $str, $matches)) {
            $duration   = $matches[1] * 3600 + $matches[2] * 60 + $matches[3];
        } else if (preg_match('/(\d+)[^0-9]+(\d+)/', $str, $matches)) {
            $duration   = $matches[1] * 60 + $matches[2];
        } else {
            // pas de durée
        }
        
        return $duration;
    }
    
    /**
     *
     * @param type $str
     * @return type 
     */
    protected final function getDistanceFromString($str)
    {            
        $matches    = array();
        $distance   = 0;
        $str        = str_replace(',', '.', $str);
        
        if (stripos($str, '1/2 Marathon') !== false || stripos($str, 'semi marathon') !== false) {
            $distance = 21.1;
        } else if (stripos($str, 'marathon') !== false) {
            $distance = 42.195;
        } else if (preg_match('/(\d+[.]?\d*)[ ]?km/i', $str, $matches)) {
            $distance = (float)$matches[1];
        } else if (preg_match('/(\d+[.]?\d*)[ ]?m/i', $str, $matches)) {
            $str = str_replace('.', '', $matches[1]);
            $distance = (float)$str / 1000.0;
        }
        
        return $distance;
    }
    
    /**
     * Récupérer les activités à partir du site bases.athle.com de la Fédération Française d'Athlétisme
     * 
     * @param string $firstname
     * @param string $lastname
     * @param enum $gender ('M', 'F')
     * @param int $birthYear
     * @param int $seasonYear
     * 
     * @return array Retourne le tableau des activités
     */
    public function getActivities($firstname, $lastname, $gender, $birthYear = null, $seasonYear = null)
    {
        $activities = array();
        $matches    = array();
        $ch         = curl_init();
        
        if ($seasonYear == null) {
            $seasonYear = (string)date('Y');
        }
        
        $firstname  = $this->stripAccents($firstname);
        $lastname   = $this->stripAccents($lastname);
        
        if (strlen($birthYear) == 4) {
            $birthYear2d = (int)substr($birthYear, 2, 2);
        } else if (strlen($birthYear) == 2) {
            $birthYear2d = (int)$birthYear;
        } else {
            $birthYear2d =  null;
        }
        
//        var_dump("http://bases.athle.com/asp.net/liste.aspx"
//                ."?frmpostback=true&frmbase=resultats&frmmode=1&frmespace=0"
//                ."&frmsaison=$seasonYear&frmnom=$lastname&frmprenom=$firstname&frmsexe=$gender/");exit;
        
        curl_setopt_array($ch, array(
            CURLOPT_URL             => "http://bases.athle.com/asp.net/liste.aspx"
                ."?frmpostback=true&frmbase=resultats&frmmode=1&frmespace=0"
                ."&frmsaison=$seasonYear&frmnom=$lastname&frmprenom=$firstname&frmsexe=$gender/",
            CURLOPT_HEADER          => 0,
            CURLOPT_RETURNTRANSFER  => 1,
            CURLOPT_HTTPGET         => true
        ));
        $res        = curl_exec($ch);
        curl_close($ch);
        
        
        if (preg_match('/id="ctnResultats">(.*)<\/table>/Ui', $res, $matches)) {
            $html   = str_replace('&nbsp;', '', $matches[1]);
            
            $html = preg_replace('#&(?=[a-z_0-9]+=)#', '&amp;', $html);
            
            $html = preg_replace('/&[^; ]{0,6}.?/e', "((substr('\\0',-1) == ';') ? '\\0' : '&amp;'.substr('\\0',1))", $html);

            //var_dump($html);exit;
            $xml    = simplexml_load_string('<table>'.$html.'</table>');
            
            foreach ($xml->tr as $tr) {
                // super test pour déterminer si la ligne en cours contient la description d'une activité
                
                if (count($tr->td) >= 10) {
                    //var_dump($tr);
                    $fullDesc       = (string)$tr->td[4];
                    //$category       = (string)$tr->td[7]; 
                    $location       = (string)$tr->td[18];
                    
                    if ($location != '') {
                        $fullDesc .= ' à '.$location;
                    }
//                    $matches = array();
//                    if ($birthYear2d && preg_match('/.*\/(\d+)/i', $category, $matches)) {
//                        if ($matches[1] && $matches[1] != $birthYear2d) {
//                             // si la date est présente dans la catégorie
//                             // et quelle est différente de celle du sportif, on saute l'activité
//                            continue;
//                        }
//                    }
                    $frDate         = (string)$tr->td[0];
                    $enDate         = '';
                    if ($frDate) {
                        $frDate     = $frDate.'/'.$seasonYear;
                        list($d, $m, $y) = explode('/', $frDate);
                        $enDate     = "$y-$m-$d";
                    }
                    
                    $name = (string)$tr->td[2]->a;
                    if ( $name == '') $name = (string)$tr->td[2];
                    
                    if ((string)$tr->td[10]->b) {
                        $time   = (string)$tr->td[10]->b;    
                    } else if ((string)$tr->td[10]->u) {
                        $time   = (string)$tr->td[10]->u;
                    } else {
                        $time   = (string)$tr->td[10]->u->b;
                    }
                    
                    $datas          = array(
                        'date'		=> $frDate,
                        'enDate'        => $enDate,
                        'name'		=> $name,
                        'descr'		=> (string)$tr->td[4],
                        'distance'      => $this->getDistanceFromString((string)$tr->td[4]),
                        'col3'		=> (string)$tr->td[6],
                        'rank'		=> (string)$tr->td[8],
                        'duration'	=> $time,
                        'secDuration'   => $this->getDurationFromString($time),
                        'col9'		=> (string)$tr->td[9],
                        'category'      => (string)$tr->td[14],
                        'col11'          => (string)$tr->td[11],
                        'location'      => $location,
                        'fullDesc'      => $fullDesc
                    );
                    
                    //var_dump($datas); 
                    //exit;
                    
                    $datas['base64Datas'] = base64_encode(serialize($datas));
                    $activities[]   = $datas;
                }
            }
            //exit;
        }
        
        return $activities;
    }
}
