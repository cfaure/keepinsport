<?php

namespace Ks\ActivityBundle\Service;

use Symfony\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Ks\ActivityBundle\Activity;

/**
 * Description of Runkeeper
 *
 * @author Ced
 */
class NikePlusService
    extends \Twig_Extension
{

    protected $_doctrine;
    
    /**
     *
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->_doctrine            = $doctrine;
    }
    
    // La méthode getName(), obligatoire
    public function getName()
    {
        return 'NikePlusService';
    }
	
    // La méthode getFunctions(), qui retourne un tableau avec les fonctions qui peuvent être appelées depuis cette extension
    public function getFunctions()
    {
        return array(
            'getUnsynchronizedActivities' => new \Twig_Function_Method($this, 'getUnsynchronizedActivities') 
        );
    }
    
    public function getUnsynchronizedActivities() {      
        $em                 = $this->_doctrine->getEntityManager();
        $activitySessionRep = $em->getRepository('KsActivityBundle:ActivitySession');
  
        
        return "SUNC OK !";
    }
    
    
    private function drawConsoleHead($title) {
        $nbCaract = 30;
        $c = "#";
        echo "\n";
        for($i=1;$i<=$nbCaract;$i++) echo $c;
        echo "\n#";
        for($i=2;$i<=($nbCaract-strlen($title)-1)/2;$i++) echo " ";
        echo " " . $title . " ";
        for($i=2;$i<=($nbCaract-strlen($title)-2)/2;$i++) echo " ";
        echo "#\n";
        for($i=1;$i<=$nbCaract;$i++) echo $c;
        echo "\n\n";
    }
    
}
