<?php

namespace Ks\NotificationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class UpdateUserMailNotificationsCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('ks:notification:updateUserMailNotifications')
            ->setDescription('Mettre à jour la configuration par mail des notifications')
            // ->addOption('serviceName', null, InputOption::VALUE_REQUIRED, 'ServiceName des activités à mettre à jour') // TODO: pas encore géré, mais pourra servir !
            ->setHelp(<<<EOT
La commande <info>ks:notification:updateUserMailNotifications</info> met à jour la configuration par mail des notifications
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
        
        $users = $userRep->findAll();
        
        $output->writeln("Mise a jour des configurations pour l'envoi de mail a chaque notification");
        
        $nbUpdate = 0;
        foreach( $users as $user ) {
            
            //On met à jour la configuration pour l'envoi de notifications par mail
            $notificationService->updateUserMailNotifications( $user );
            $nbUpdate += 1;
            $output->writeln("mise a jour pour l'utilisateur " . $user->getUsername() ." Ok");
        }
          
        $output->writeln("Mise a jour terminee.");
        $output->writeln($nbUpdate . "utilisateurs traites.");
            
        $em->flush();
    }
    
}
