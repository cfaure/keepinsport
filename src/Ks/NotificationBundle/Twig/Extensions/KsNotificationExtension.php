<?php

namespace Ks\NotificationBundle\Twig\Extensions;

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Bridge\Twig\Extension\TranslationExtension;

class KsNotificationExtension extends \Twig_Extension
{    
    public function getFilters()
    {
        return array(
            'created_ago'       => new \Twig_Filter_Method($this, 'createdAgo'),
            'created_ago_str'   => new \Twig_Filter_Method($this, 'createdAgoFromString'),
            'getIds'            => new \Twig_Filter_Method($this, 'extractNotificationsId'),
        );
    }
    
    /**
     * 
     * @param type $str
     * @return type
     */
    public function createdAgoFromString($str)
    {
        return $this->createdAgo(new \DateTime($str));
    }

    /**
     * 
     * @param \DateTime $dateTime
     * @return type
     */
    public function createdAgo(\DateTime $dateTime)
    {
        $ts     = $dateTime->getTimestamp();
        $delta  = time() - $ts;
        $format = null;
        $prettyOnUnit = '';
        
        date_default_timezone_set('Europe/Paris');
        setlocale(LC_TIME, 'fr_FR.utf8','fra'); // FIXME: !!! ... pour strftime
        
        $isInFutur = false;
        
        if ($delta < 0) {
            $delta = abs($delta);
            $isInFutur = true;
            /*$time = $delta;
            $unit       = "futur";
            $prettyOn   = 'dans le futur...';*/
        }
        if ($delta < 5) {
            $time       = $delta;
            $unit       = "second" . (($time > 1) ? "s" : "");
            $prettyOn   = 'à l\'instant';
        } else if ($delta < 60) {
            // Seconds
            $time       = $delta;
            $unit       = "second" . (($time > 1) ? "s" : "");
            $prettyOn   = $isInFutur ? 'dans '.$delta : 'il y a '.$delta;
            $prettyOnUnit =  $unit;
        } else if ($delta <= 3600) {
            // Mins
            $time       = floor($delta / 60);
            $unit       = "minute" . (($time > 1) ? "s" : "");
            $prettyOn   = $isInFutur ? 'dans '.$time : 'il y a '.$time;
            $prettyOnUnit =  $unit;
        } else if ($delta <= 86400) {
            // Hours
            $time       = floor($delta / 3600);
            $unit       = "hour" . (($time > 1) ? "s" : "");
            $prettyOn   = $isInFutur ? 'dans '.$time : 'il y a '.$time;
            $prettyOnUnit =  $unit;
        } else if ($delta <= 2592000) {
            // Days
            $time       = floor($delta / 86400);
            $unit       = "day" . (($time > 1) ? "s" : "");
            $format     = '%e %b';
        } else if ($delta <= 31536000) {
            // Months
            $time       = floor($delta / 2592000);
            $unit       = "month".(($time > 1) ? "s" : "");
            $format     = '%e %b';
        } else {
            // Years
            $time       = floor($delta / 31536000);
            $unit       = "year".(($time > 1) ? "s" : "");
            $format     = '%e %b %Y';
        }
        
        if ($format) {
            // NOTE CF: %e pas compatible sous windows...
            if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
                $format = preg_replace('#(?<!%)((?:%%)*)%e#', '\1%#d', $format);
            }
            $prettyOn       = 'le '.strftime($format, $ts);
            $prettyOnUnit   =  '';
        }

        return array(
            'unit'          => $unit,
            'time'          => $time,
            'pretty'        => $prettyOn,
            'prettyUnit'    => $prettyOnUnit
        );
    }
    
    public function extractNotificationsId($notifications) {
        $notificationsId = array();
        foreach( $notifications as $notification ) {
            $notificationsId[] = $notification->getId();
        }
        
        return $notificationsId;
    }
    

    public function getName()
    {
        return 'ks_notification_extension';
    }
}
?>
