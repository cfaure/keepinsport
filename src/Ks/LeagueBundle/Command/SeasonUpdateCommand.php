<?php

namespace Ks\LeagueBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SeasonUpdateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('ks:league:seasonUpdate')
            ->setDescription('mise à jour de fin de saison')
            ->setDefinition(array(
                new InputArgument('testOnly', InputArgument::OPTIONAL, 'testOnly'),
            ))
            ->setHelp(<<<EOT
La commande <info>ks:league:seasonUpdate</info> permet la mise à jour à la fin de chaque saison des ligues Keepinsport :

  <info>php app/console ks:league:seasonUpdate</info>
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
        
        $month = date('m',mktime(12, 0, 0, date("m"),1, date("Y")));
        
        $ll->seasonUpdate($month, $isTestOnly);
        
        ob_end_flush(); 
    }
}
