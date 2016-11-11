<?php

namespace Ks\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class RunJobCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('ks:user:runjob')
            ->setDescription('Cherche l\'existence de fichier .job, les lis afin d\importer des activités sportives en masse')
            ->setHelp(<<<EOT
La commande <info>ks:user:runjob</info> synchronise les activités via l'execution d'une ou plusieurs commande situées dans le/les fichiers .job d'un utilisateur avec son profil Keepinsport :

  <info>php app/console ks:user:runjob</info>
EOT
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        
        // Recherche de fichiers dans un répertoire définis ou se situe les jobs 
        // Jobs Runkeeper
 
        $servicesToSynchronize = array(
            'googleagenda',
            'runkeeper',
            'nikeplus',
            'endomondo',
            'suunto'
        );
        
        foreach( $servicesToSynchronize as $serviceToSynchronize ) {
            $dirname = $this->getContainer()->get('kernel')->getRootdir().'/jobs/pending/' . $serviceToSynchronize .'/';
            $dir = opendir($dirname); 
            while($file = readdir($dir)) {
                echo "file".$file;
                if($file != '.' && $file != '..' && !is_dir($dirname.$file))
                {
                    $filepath = $this->getContainer()->get('kernel')->getRootdir().'/jobs/pending/' . $serviceToSynchronize .'/';
                    $jobFile = fopen($filepath.$file, 'r');
                    $ligne = fgets($jobFile);
                    echo "ligne".$ligne;
                    if(!empty($ligne)){
                        echo "execution de la ligne : \n". $ligne. "\n";
                        exec($ligne);
                    }

                    fclose($jobFile);
                }
            }
            closedir($dir);
        }
        
        // Jobs googleAgenda
        /*$dirname = $this->getContainer()->get('kernel')->getRootdir().'/jobs/pending/nikeplus/';
        $dir = opendir($dirname); 
        while($file = readdir($dir)) {
            if($file != '.' && $file != '..' && !is_dir($dirname.$file))
            {
                $filepath = $this->getContainer()->get('kernel')->getRootdir().'/jobs/pending/nikeplus/';
                $jobFile = fopen($filepath.$file, 'r');
                $ligne = fgets($jobFile);
                if(!empty($ligne)){
                    echo "execution de la ligne : \n". $ligne. "\n";
                    
                    exec($ligne);
                }

                fclose($jobFile);
            }
        }
        closedir($dir);*/
        
        // Jobs googleAgenda
        /*$dirname = $this->getContainer()->get('kernel')->getRootdir().'/jobs/pending/googleagenda/';
        $dir = opendir($dirname); 
        while($file = readdir($dir)) {
            if($file != '.' && $file != '..' && !is_dir($dirname.$file))
            {
                $filepath = $this->getContainer()->get('kernel')->getRootdir().'/jobs/pending/googleagenda/';
                $jobFile = fopen($filepath.$file, 'r');
                $ligne = fgets($jobFile);
                if(!empty($ligne)){
                    exec($ligne);
                }

                fclose($jobFile);
            }
        }
        closedir($dir);*/
        
        

        
    }
    
    
    
  
    
}
