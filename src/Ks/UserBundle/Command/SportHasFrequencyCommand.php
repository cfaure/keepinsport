<?php

namespace Ks\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class SportHasFrequencyCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('ks:user:sportHasFrequency')
            ->setDescription('Cherche l\'existence d\'activité à créer selon une fréquence pour chaque user')
            ->setHelp(<<<EOT
La commande <info>ks:user:sportHasFrequency</info> permet de créer des activités selon une fréquence précisé pour un user et un sport donné :

  <info>php app/console ks:user:sportHasFrequency</info>
EOT
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em                         = $this->getContainer()->get('doctrine')->getEntityManager();
        $notificationService        = $this->getContainer()->get('ks_notification.notificationService');
        $activityService            = $this->getContainer()->get('ks_activity.activityService');
        $leagueLevelService         = $this->getContainer()->get('ks_league.leagueLevelService');
        $userRep                    = $em->getRepository('KsUserBundle:User');
        $sportRep                   = $em->getRepository('KsActivityBundle:Sport');
        $userHasSportFrequencyRep   = $em->getRepository('KsUserBundle:UserHasSportFrequency');
        $activityRep                = $em->getRepository('KsActivityBundle:Activity');
        
        $testOnly = false;
        $output->writeln("Traitement frequence creation activite /user,sport (testOnly :" . $testOnly . ")");
        
        $userHasSportFrequency = $userHasSportFrequencyRep->getUserHasSportFrequencyToProcess();
        
        if (is_array($userHasSportFrequency) && $userHasSportFrequency != null) {
        
            $keepinsportUser = $userRep->findOneByUsername( "keepinsport" );

            $notifications = null;

            foreach( $userHasSportFrequency as $parameter ) {
                //On crée une activité à valider pour l'utilisateur à partir de sa dernière activitée postée selon son sport paramétré
                $user = $userRep->find($parameter['user_id']);
                $sport = $sportRep->find($parameter['sport_id']);
                $output->writeln("----User/sport : ".$user->getId()."/".$sport->getId());

                $lastActivity = $activityRep->getLastActivityFromUserBySport($user, $sport);
                $output->writeln("----Last activity found : ".$lastActivity->getId());

                if (!$testOnly) {
                    $newActivity = $activityService->duplicateActivityForUserHasSportFrequency($lastActivity, $user);
                    $output->writeln("----New activity published : ".$newActivity->getId());

                    //On lui fait gagner les points qu'il doit
                    $leagueLevelService->activitySessionEarnPoints($newActivity, $user);

                    $message = "Tu avais planifié une activité sportive (". $sport->getLabel()."), l'as-tu réellement réalisée ?";
                    $notifications[] = array(
                        "fromUser"              => $keepinsportUser,
                        "toUser"                => $user,
                        "message"               => $message,
                        "newActivity"           => $newActivity
                    );
                    
                    $output->writeln("-------------------------");
                }
            }

            if (is_array($notifications) && $notifications !=null) {
                foreach( $notifications as $notification ) {
                    //var_dump($notification["toUser"]->getId());
                    $notificationService->sendNotification(
                        $notification["newActivity"], 
                        $notification["fromUser"], 
                        $notification["toUser"], 
                        "userHasSportFrequency", 
                        $notification["message"]
                    );
                }
            }
        }
        else {
            $output->writeln("----Aucun parametrage trouve...");
        }
    }
}
