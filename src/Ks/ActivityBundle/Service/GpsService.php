<?php

namespace Ks\ActivityBundle\Service;

use Symfony\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Ks\ActivityBundle\Activity;

/**
 * Description of GpsService
 *
 * @author Laurent
 */
class GpsService
    extends \Twig_Extension
{
    
    // La méthode getName(), obligatoire
    public function getName()
    {
        return 'GpsService';
    }
	
    // La méthode getFunctions(), qui retourne un tableau avec les fonctions qui peuvent être appelées depuis cette extension
    public function getFunctions()
    {
        return array(
            'getDistance' => new \Twig_Function_Method($this, 'getDistance') 
        );
    }
    /*
     * Format des points 
     * $waypoint1["lat"], $waypoint1["lon"], $waypoint1["ele"] 
     * 
     Calcule la distance entre 2 points en prenant en compte l'altitude */
    public function getDistance($waypoint1, $waypoint2) {     
        $dxy2 = pow( $this->dxy($waypoint1, $waypoint2), 2 );
        $z = pow( $waypoint1["ele"] - $waypoint2["ele"], 2 );
        $d = sqrt( $dxy2 + $z );
        return $d;
        
    }
    
    /*Fonction 1 permettant le calcul des distances */
    public function dxy($waypoint1, $waypoint2) {      
        
        return 6366000*acos(
                        sin($this->degreeToRadian($waypoint1["lat"]))
                        * sin($this->degreeToRadian($waypoint2["lat"]))
                        + cos($this->degreeToRadian($waypoint1["lat"]))
                        * cos($this->degreeToRadian($waypoint2["lat"]))
                        * cos($this->degreeToRadian($waypoint1["lon"] - $waypoint2["lon"]))
                    );
        
        
  
    }
    
    public function degreeToRadian($degree){      
        return $degree*pi()/180;
    }
    
    /*
     * Format des points 
     * $waypoint1["lat"] 
     * $waypoint1["lon"] 
     * $waypoint1["ele"] 
     * le tableau doit commencer à 1; 
     * @return distance en mètres 
     * Calcule la distance totale entre un tableau de points (corresponda la distance d'un tracé)
     */
   
    public function getTotalDistance($aWp){
        $nbPoints = count($aWp);
        $distance = 0;
        for($i=1;$i<$nbPoints;$i++){
            
            $waypoint1["lat"] =  $aWp[$i]["lat"]; 
            $waypoint1["lon"] =  $aWp[$i]["lon"]; 
            $waypoint1["ele"] =  $aWp[$i]["ele"];

            $j = $i+1;

            $waypoint2["lat"] =  $aWp[$j]["lat"]; 
            $waypoint2["lon"] =  $aWp[$j]["lon"]; 
            $waypoint2["ele"] =  $aWp[$j]["ele"];


            $distance  = $distance + $this->getDistance($waypoint1,$waypoint2);

       }
       
       return $distance;
    }
    
      /*
     * Format des points 
     * $waypoint1["lat"] 
     * $waypoint1["lon"] 
     * $waypoint1["ele"] 
     * le tableau doit commencer à 1; 
     * @return distance en mètres 
     * Calcule la distance totale entre un tableau de points (corresponda la distance d'un tracé)
     */
   
    public function getElevationGain($aWp){
        $nbPoints = count($aWp);
        $elevationGain = 0;
        for($i=1;$i<$nbPoints;$i++){
            $j = $i+1;
            $waypoint1["ele"] =  $aWp[$i]["ele"];
            $waypoint2["ele"] =  $aWp[$j]["ele"];

            if( $waypoint1["ele"] < $waypoint2["ele"]){
                $elevationGain = $elevationGain + ($waypoint2["ele"] - $waypoint1["ele"]);
            }
       }
       return $elevationGain;
    }
    
     /*
     * Format des points 
     * $waypoint1["lat"] 
     * $waypoint1["lon"] 
     * $waypoint1["ele"] 
     * le tableau doit commencer à 1; 
     * @return distance en mètres 
     * Calcule la distance totale entre un tableau de points (corresponda la distance d'un tracé)
     */
   
    public function getElevationLost($aWp){
        $nbPoints = count($aWp);
        $elevationLost = 0;
        for($i=1;$i<$nbPoints;$i++){
            $j = $i+1;
            $waypoint1["ele"] =  $aWp[$i]["ele"];
            $waypoint2["ele"] =  $aWp[$j]["ele"];

            if( $waypoint1["ele"] > $waypoint2["ele"]){
                $elevationLost = $elevationLost + ($waypoint1["ele"] - $waypoint2["ele"]);
            }
            
       }
       return $elevationLost;
    }
    
    public function validate($xml)
    {
        libxml_use_internal_errors(true);

        $doc = new \DOMDocument('1.0', 'utf-8');
        $doc->loadXML($xml);

        $errors = libxml_get_errors();
        if (empty($errors))
        {
        return true;
        }

        $error = $errors[ 0 ];
        if ($error->level < 3)
        {
        return true;
        }

        $lines = explode("r", $xml);
        $line = $lines[($error->line)-1];

        $message = $error->message.' at line '.$error->line.':
        '.htmlentities($line);

        return $message;
  }
           
    
    
    
    
    
    
    
    
 
   
    

    
}
