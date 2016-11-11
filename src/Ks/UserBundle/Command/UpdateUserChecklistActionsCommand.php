<?php

namespace Ks\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class UpdateUserChecklistActionsCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('ks:user:updateUserChecklistActions')
            ->setDescription('Mettre à jour les actions à effectuer par les utilisateurs')
            // ->addOption('serviceName', null, InputOption::VALUE_REQUIRED, 'ServiceName des activités à mettre à jour') // TODO: pas encore géré, mais pourra servir !
            ->setHelp(<<<EOT
La commande <info>ks:user:updateUserChecklistActions</info> met à jour les actions à effectuer par les utilisateurs
EOT
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em                     = $this->getContainer()->get('doctrine')->getEntityManager();
        $userRep                = $em->getRepository('KsUserBundle:User');
        $checklistActionRep     = $em->getRepository('KsUserBundle:ChecklistAction');
        $userChecklistActionRep = $em->getRepository('KsUserBundle:UserHasToDoChecklistAction');
        
        $checklistActions = $checklistActionRep->findAll();
        
        $users = $userRep->findAll();
        
        $output->writeln("Mise a jour des configurations pour l'envoi de mail a chaque notification");
        
        $nbUpdate = 0;
        foreach( $users as $user ) {
            $output->writeln($user->getUsername());
            foreach( $checklistActions as $checklistAction ) {

                $userHasToDoChecklistAction = $userChecklistActionRep->findOneBy( 
                    array(
                        "user" => $user->getId(),
                        "checklistAction" => $checklistAction->getId()
                    )
                );

                if( !is_object( $userHasToDoChecklistAction ) ) {
                    $userHasToDoChecklistAction = new \Ks\UserBundle\Entity\UserHasToDoChecklistAction();
                    $userHasToDoChecklistAction->setUser( $user );
                    $userHasToDoChecklistAction->setChecklistAction( $checklistAction );

                    $em->persist( $userHasToDoChecklistAction );

                    $em->flush();
                    
                    $nbUpdate += 1;
                    $output->writeln("   - " . $checklistAction->getLabel());
                }
            }
            
        }
          
        $output->writeln("Mise a jour terminee.");
        $output->writeln($nbUpdate . " mise à jour.");
            
        $em->flush();
    }
    
}
