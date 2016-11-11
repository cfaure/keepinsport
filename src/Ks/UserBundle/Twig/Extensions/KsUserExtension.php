<?php

namespace Ks\UserBundle\Twig\Extensions;


class KsUserExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            'age'               => new \Twig_Filter_Method($this, 'age'),
            'hourMinutes'       => new \Twig_Filter_Method($this, 'hourMinutes'),
            'user_link'         => new \Twig_Filter_Method($this, 'userLink'),
            'metersToKm'        => new \Twig_Filter_Method($this, 'metersToKm'),
            'substr'            => new \Twig_Filter_Method($this, 'substr'),
            'participatesInArticleSportEvent'     => new \Twig_Filter_Method($this, 'userParticipatesInArticleSportEvent'),
            'couldInvitOther'               => new \Twig_Filter_Method($this, 'couldInvitOther'),
            'split'       => new \Twig_Filter_Method($this, 'explode_custom'),
            'isEnabled'       => new \Twig_Filter_Method($this, 'serviceIsEnabled'),
        );
    }

    public function age($dateOfBirth)
    {
        list($annee, $mois, $jour) = explode('-', date('Y-m-d', $dateOfBirth->getTimestamp()));
        $today = array();
        $today['mois'] = date('n');
        $today['jour'] = date('j');
        $today['annee'] = date('Y');
        $age = $today['annee'] - $annee;
        if ($today['mois'] <= $mois) {
            if ($mois == $today['mois']) {
                if ($jour > $today['jour'])
                    $age--;
            }
            else
                $age--;
        }
            
        return $age;
    }
    /*param duration in seconds*/
    public function hourMinutes($duration)
    {   
        $stringHeure = "";
        $heure = intval(abs($duration / 3600));
        $duration = $duration - ($heure * 3600);
        $minute = intval(abs($duration / 60));
        $duration = $duration - ($minute * 60);
        $seconde = round($duration);
        if($heure!=0){
            $stringHeure = "$heure h :";
        }
        
        return "$stringHeure $minute m : $seconde s";
    }
    
    /*param duration in seconds*/
    public function metersToKm($meters)
    {   
        $km = round($meters)/1000;
        $km = round($km, 3);
        return $km;
    }
    
    public function substr($string,$start,$length)
    {
        return substr ( $string , $start , $length );
    }
    
    
    
    public function userLink($userId)
    {   
        return "";
    }

    public function getName()
    {
        return 'ks_user_extension';
    }
    

    function userParticipatesInArticleSportEvent( $user, $sportEvent ) {
        foreach($user->getArticleSportingEventsParticipations() as $userSportEvent ) {
            if( $userSportEvent->getId() == $sportEvent->getId()) {
                return true;
            }
        }
        return false;
    }
    
    public function explode_custom($string,$split)
    {
        $data = explode($split, $string);
        return $data;
    }
    
    function serviceIsEnabled( $service, $user ) {
        foreach($user->getServices() as $userHasService ) {
            if( $userHasService->getService()->getId() == $service->getId() && $userHasService->getIsActive()) {
                return true;
            }
        }
        return false;
    }
    
}
?>
