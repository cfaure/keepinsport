<?php

namespace Ks\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class DeleteUserCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('ks:user:delete')
            ->setDescription('Supprimer un utilisateur')
            ->setDefinition(array(
                new InputArgument('userId', InputArgument::REQUIRED, 'User id'),
            ))
            ->setHelp(<<<EOT
La commande <info>ks:user:delete</info> supprime un utilisateur
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
        $userId    = $input->getArgument('userId');
        
        $user = $userRep->find($userId);
        
        if( !is_object( $user ) ) {
            $output->writeln("Impossible de trouver l utilisateur " . $userId);
        } else {
            $em->remove( $user );
            $em->flush();
            
            $output->writeln("Utilisateur supprime avec success");
        }  
    }
    
}
