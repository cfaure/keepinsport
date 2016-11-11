<?php

namespace Ks\LeagueBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WeeklyUpdateLeagueLeagueCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('ks:league:weeklyUpdate')
            ->setDescription('updated weekly league level for each user')
            /*->setDefinition(array(
                new InputArgument('beginDate', InputArgument::REQUIRED, 'Date de début au format d/m/Y'),
                new InputArgument('beginTime', InputArgument::REQUIRED, 'Heure de début au format H:i:s'),
            ))*/
            ->setHelp(<<<EOT
La commande <info>ks:league:weeklyUpdate</info> permet la mise à jour hebdomadaire des ligues Keepinsport :

  <info>php app/console ks:league:weeklyUpdate</info>
EOT
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ob_start();
        try {
            
            $ll   = $this->getContainer()->get('ks_league.leagueLevelService');
            //$beginSeasonDateFr = $this->getContainer()->getParameter('beginSeasonDateFr');
            //$beginningOfSeason = \DateTime::createFromFormat('d/m/Y H:i:s', $beginSeasonDateFr);
            
            /*$beginDateString = $input->getArgument('beginDate');
            $beginTimeString = $input->getArgument('beginTime');
            $beginDate = \DateTime::createFromFormat('d/m/Y H:i:s', $beginDateString." ".$beginTimeString);*/
            $text = $ll->weeklyUpdate();
            
            //$notificationService  = $this->get('ks_notification.notificationService');
            //$notificationService->sendNotification($activity, $user, $activity->getUser(), "share");
            //$date = new \DateTime("2012-05-13T13:52:08Z");
            //$text =  $date->getTimestamp();
            //echo $text;
            //$output->writeln($text);
            ob_end_flush(); 
        } catch (\Exception $e) {
            throw $e;
            ob_end_flush(); 
        }
        
        //
    }
}
