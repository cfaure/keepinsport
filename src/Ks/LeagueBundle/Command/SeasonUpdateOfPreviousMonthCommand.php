<?php

namespace Ks\LeagueBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SeasonUpdateOfPreviousMonthCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('ks:league:seasonUpdateOfPreviousMonth')
            ->setDescription('mise à jour de fin de saison du mois précédent')
            ->setDefinition(array(
                new InputArgument('testOnly', InputArgument::OPTIONAL, 'testOnly'),
            ))
            ->setHelp(<<<EOT
La commande <info>ks:league:seasonUpdateOfPreviousMonth</info> permet la mise à jour saisonnière des ligues Keepinsport :

  <info>php app/console ks:league:seasonUpdateOfPreviousMonth</info>
EOT
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $testOnly                     = $input->getArgument('testOnly');
        if ($testOnly) {
            $isTestOnly = (boolean)$testOnly;
        } else {
            $isTestOnly = false;
        }
        
        ob_start();
        $ll   = $this->getContainer()->get('ks_league.leagueLevelService');
        
        $previousMonth = date('m',mktime(12, 0, 0, date("m")-1,1, date("Y")));
        
        $ll->seasonUpdate($previousMonth, $isTestOnly);
        
        ob_end_flush(); 
    }
}
