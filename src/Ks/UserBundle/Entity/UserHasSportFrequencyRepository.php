<?php

namespace Ks\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * UserHasSportFrequencyRepository
 *
 */
class UserHasSportFrequencyRepository extends EntityRepository
{
    /**
     * Récupère un tableau des user/sport à traiter par SportHasFrequencyCommand.php
     * 
     * @return array
     */
    public function getUserHasSportFrequencyToProcess()
    {
        $tab_jours = array('isScheduledOnSun', 'isScheduledOnMon', 'isScheduledOnTue', 'isScheduledOnWed', 'isScheduledOnThu', 'isScheduledOnFri', 'isScheduledOnSat');
        $tag = $tab_jours[date('w', mktime(0,0,0,date('m'),date('d'),date('Y')))];
        
        $time = date('H:m');
        
        $dbh        = $this->_em->getConnection();
        $uhsf = $dbh->executeQuery(
            'SELECT uhsf.user_id, uhsf.sport_id'
            .' FROM ks_user_has_sport_frequency uhsf'
            .' WHERE uhsf.mailtime <= ?'
            .' AND NOT EXISTS (SELECT 1 
                                 FROM ks_notification, ks_activity a 
                                WHERE type_id = 22 
                                  AND activity_id = a.id 
                                  AND a.sport_id = uhsf.sport_id 
                                  AND a.user_id = uhsf.user_id
                                  AND DATE_FORMAT(createdAt,\'%m-%d-%Y\') = DATE_FORMAT(NOW(),\'%m-%d-%Y\'))'
            . ($tag == 'isScheduledOnMon' ? ' AND uhsf.isScheduledOnMon = 1' : '')
            . ($tag == 'isScheduledOnTue' ? ' AND uhsf.isScheduledOnTue = 1' : '')
            . ($tag == 'isScheduledOnWed' ? ' AND uhsf.isScheduledOnWed = 1' : '')
            . ($tag == 'isScheduledOnThu' ? ' AND uhsf.isScheduledOnThu = 1' : '')
            . ($tag == 'isScheduledOnFri' ? ' AND uhsf.isScheduledOnFri = 1' : '')
            . ($tag == 'isScheduledOnSat' ? ' AND uhsf.isScheduledOnSat = 1' : '')
            . ($tag == 'isScheduledOnSun' ? ' AND uhsf.isScheduledOnSun = 1' : '')
            .' ORDER BY uhsf.user_id, uhsf.sport_id',
            array($time)
        );
        
        return $uhsf->fetchAll(\PDO::FETCH_ASSOC);
    }
    
}
