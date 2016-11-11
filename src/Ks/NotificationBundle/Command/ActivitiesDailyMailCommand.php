<?php

namespace Ks\NotificationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class ActivitiesDailyMailCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('ks:notification:activitiesDailyMail')
            ->setDescription('Envoyer par mail un récaputilatif des activites de la communaute de la journee')
                ->addOption('date', null, InputOption::VALUE_OPTIONAL, 'date des activites (Y-m-d)')
            // ->addOption('serviceName', null, InputOption::VALUE_REQUIRED, 'ServiceName des activités à mettre à jour') // TODO: pas encore géré, mais pourra servir !
            ->setHelp(<<<EOT
La commande <info>ks:notification:activitiesDailyMail</info> permet d'envoyer par mail un récaputilatif des activites de la communaute de la journee
EOT
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em        = $this->getContainer()->get('doctrine')->getEntityManager();
        $userRep   = $em->getRepository('KsUserBundle:User');
        
        //Services
        $notificationService  = $this->getContainer()->get('ks_notification.notificationService');
        
        //Récupération des paramètres
        //$date     = $input->getOption('date');
        
        //if( !isset( $date ) || empty( $date )) $date = date("Y-m-d"); //date du jour
        //echo $date;
        //Le script s'envoie le matin
        //On prend le jour d'avant
        //$date = date("Y-m-d", strtotime($date ." -1 day"));
        //echo $date;
        $users = $userRep->findAll();
        
        $output->writeln("Debut d'envoi des mails");
        
        $nbSend = 0;
        foreach( $users as $user ) {
            
            //On met à jour la configuration pour l'envoi de notifications par mail
            $result = $notificationService->sendActivitiesDailyMail( $user );
            
            if( $result ) {
                $nbSend += 1;
                $output->writeln("Envoi pour l'utilisateur " . $user->getUsername() ." Ok");
            }
        }
          
        $output->writeln("Envois termines");
        $output->writeln($nbSend . " mails envoyes.");
            
        $em->flush();
    }
    
}
