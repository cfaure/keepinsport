<?php

namespace Ks\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class GoogleAgendaSyncCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('ks:user:googleagendasync')
            ->setDescription('Synchroniser les événements d\'un utilisateur Google Agenda')
            ->setDefinition(array(
                new InputArgument('idUserAndIdService', InputArgument::REQUIRED, 'Id User And Id Service'),
            ))
            ->setHelp(<<<EOT
La commande <info>ks:user:googleagendasync</info> synchronise les événements Google Agenda d'un utilisateur avec son agenda Keepinsport :

  <info>php app/console ks:user:googleagendasync idUserAndIdService</info>
EOT
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $idUserAndIdService         = $input->getArgument('idUserAndIdService');
        $em                         = $this->getContainer()->get('doctrine')->getEntityManager();
        $UserHasServicesRep         = $em->getRepository('KsUserBundle:UserHasServices');
        $userRep                    = $em->getRepository('KsUserBundle:User');
        $eventRep                   = $em->getRepository('KsEventBundle:Event');
        $agendaHasEventRep          = $em->getRepository('KsAgendaBundle:AgendaHasEvents');

        if(file_exists($this->getContainer()->get('kernel')->getRootdir().'/jobs/pending/googleagenda/'.$idUserAndIdService.'.job')){
              rename($this->getContainer()->get('kernel')->getRootdir().'/jobs/pending/googleagenda/'.$idUserAndIdService.'.job', $this->getContainer()->get('kernel')->getRootdir().'/jobs/inprogress/googleagenda/'.$idUserAndIdService.'.job');
        }
        
        //Récupération du token via l'id du service et de l'user
        $posUderscore = strpos($idUserAndIdService , "_" );
        $lengthIdUser = $posUderscore - 7;
        $idUser = substr($idUserAndIdService, 7, $lengthIdUser);
        $posIdService = strpos($idUserAndIdService , "serviceid-" );
        $posIdService = $posIdService + 10; 
        $longString = strlen($idUserAndIdService);
        $longIdService = $longString - $posIdService;
        $idService = substr($idUserAndIdService, $posIdService, $longIdService);
        
        $UserHasServices    = $UserHasServicesRep->findOneBy(array("service"=>$idService , "user"=>$idUser));
        $token              = $UserHasServices->getToken();
        $lastSync           = $UserHasServices->getLastSyncAt();
        
        
        $output->writeln("Isset token(=$token) ? : " . isset($token) );
        
        if(isset($token)){
            $client = \Zend_Gdata_AuthSub::getHttpClient($token);
            $cal = new \Zend_Gdata_Calendar($client);
        }else{
            $output->writeln($token);
        }
        
        if(isset($lastSync)){
            $firstSync = false;
        }else{
            $firstSync = true;
        }
        
        $user               = $userRep->find($idUser);
       
        $agenda             = $user->getAgenda();
         
        //On récupère tous les événements , ou uniquement a partir de la dernière synchro
        if($firstSync){
            $agendaHasEvents    = $agendaHasEventRep->findBy(array("agenda"=>$agenda->getId()));
        }else{
            $agendaHasEvents    = $userRep->getAgendaHasEventAfterDateTime($agenda->getId() , $lastSync);
        }
        
        $aLogEvent =null;
        $output->writeln("Synchro de KS vers Google Agenda...");
        if (isset($agendaHasEvents) && is_array($agendaHasEvents) && count($agendaHasEvents) >0) {
            //var_dump($agendaHasEvents);
            $aLogEvent = $eventRep->syncKeepinToGoogle($agendaHasEvents , $cal);
        }
        $output->writeln("FIN - Synchro de KS vers Google Agenda !");
        $datetimeNow = new \DateTime('now');
        $now = $datetimeNow->format("d-m-Y_h-i-s");
        $fileLog = fopen ($this->getContainer()->get('kernel')->getRootdir().'/jobs/logs/googleagenda/'.$idUserAndIdService.'-'.$now.'.log', "a+");
        if (!is_null($aLogEvent)) {
            foreach($aLogEvent as $logEvent){
                $output->writeln($logEvent);
                fputs($fileLog, $logEvent." \n");
            }
        }
        $output->writeln("Synchro de Google Agenda vers KS");
        $aLogEventGoogle = $eventRep->syncGoogleToKeepin($cal, $lastSync , $user);
        $output->writeln("FIN - Synchro de Google Agenda vers KS !");
        foreach($aLogEventGoogle as $logEventGoogle){
             $output->writeln("     ".$logEventGoogle);
             fputs($fileLog, $logEventGoogle." \n");
        }
        
        $UserHasServices->setFirstSync($firstSync);
        $UserHasServices->setLastSyncAt(new \DateTime('now'));
        $UserHasServices->setStatus('done');
        $em->persist($UserHasServices);
        $em->flush();
        
        //on déplace le fichier qui a servit a faire l'import asynchrone 
        if(file_exists($this->getContainer()->get('kernel')->getRootdir().'/jobs/inprogress/googleaganda/'.$idUserAndIdService.'.job')){
            rename($this->getContainer()->get('kernel')->getRootdir().'/jobs/inprogress/googleagenda/'.$idUserAndIdService.'.job', $this->getContainer()->get('kernel')->getRootdir().'/jobs/done/googleagenda/'.$idUserAndIdService.'.job');
        }
        
    }
    
    
    
    public function secondesToTimeDuration($duration){
        $heure = intval(abs($duration / 3600));
        $duration = $duration - ($heure * 3600);
        $minute = intval(abs($duration / 60));
        $duration = $duration - ($minute * 60);
        $seconde = round($duration);
        $time = new \DateTime("$heure:$minute:$seconde");
        //$time = "$heure:$minute:$seconde";
        return $time;
    }   
    
    
    public function wd_remove_accents($str, $charset='utf-8')
   {
        $str = htmlentities($str, ENT_NOQUOTES, $charset);

        $str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
        $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères

        return $str;
   }
   
   
    public function formatNameSport($sport) {
        return str_replace (" " , "-" , strtolower($this->wd_remove_accents($sport)) );
    }
    
}

