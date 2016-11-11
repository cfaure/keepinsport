<?php

namespace Ks\ActivityBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TcxImportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();
        
        $this
            ->setName('ks:activity:tcximport')
            ->setDescription('import from tcx file')
            ->setDefinition(array(
                new InputArgument('userId', InputArgument::REQUIRED, 'User Id'),
                new InputArgument('filename', InputArgument::REQUIRED, 'TCX Filename'),
            ))
            ->setHelp(<<<EOT
La commande <info>ks:activity:tcximport</info> permet d'importer un fichier tcx pour un utilisateur :

  <info>php app/console ks:activity:tcximport</info>
EOT
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $userId         = $input->getArgument('userId');
        $filename       = $input->getArgument('filename');
        $em             = $this->getContainer()->get('doctrine')->getEntityManager();
        $importService  = $this->getContainer()->get('ks_activity.importActivityService');
        $tcxDatas       = file_get_contents($filename);
        $user           = $em->find('KsUserBundle:User', $userId);
        
        $activityDatas  = $importService->buildJsonToSave($user, array('xml' => $tcxDatas), 'garmin');
        $session        = $importService->saveUserSessionFromActivityDatas($activityDatas, $user);
    }
}
