<?php

namespace Ks\ActivityBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class UpdateTrackingDatasCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('ks:activity:updateTrackingDatas')
            ->setDescription('Mettre à jour le champ trackingDatas des activités')
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'Id de l\'activité à mettre à jour')
            // ->addOption('serviceName', null, InputOption::VALUE_REQUIRED, 'ServiceName des activités à mettre à jour') // TODO: pas encore géré, mais pourra servir !
            ->setHelp(<<<EOT
La commande <info>ks:activity:updateSessionDetails</info> met à jour le champ "trackingDatas" des activités à partir
    des données récupérées par le service, ou à partir d'un fichier gpx (ou autre format)
EOT
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em             = $this->getContainer()->get('doctrine')->getEntityManager();
        $activityId     = $input->getOption('id');
        $activityRepo   = $em->getRepository('KsActivityBundle:ActivitySession');
        $equipmentRep   = $em->getRepository('KsUserBundle:Equipment');
        
        if ($activityId > 0) {
            $activities = $activityRepo->findById($activityId);
        } else {
            $activities = $activityRepo->findAll();
        }
        $importService  = $this->getContainer()->get('ks_activity.importActivityService');
       
        foreach ($activities as $activity) {
            $trackingDatas      = array();
            $sportLabel         = $activity->getSport() ? $activity->getSport()->getLabel() : 'n/a';
            $activityServices   = $activity->getServices();
            $activityService    = count($activityServices) > 0 ? $activityServices[0] : null;
            $user               = $activity->getUser();

            if ($activityService != null) {
                $activityServiceName = strtolower($activityService->getService()->getName());
            } else {
                $gpx = $em->getRepository('KsActivityBundle:Gpx')->findOneByActivity($activity->getId());
                if ($gpx !== null) {
                    $activityServiceName = 'gpx';    
                } else {
                    $activityServiceName = 'manual';
                    // NOTE CF: cas non géré
                    continue;
                }
            }
            
            switch ($activityServiceName) {
                case 'runkeeper':
                case 'nikeplus':
                case 'endomondo':
                case 'suunto' :
                    $serviceDatas = json_decode($activityService->getSourceDetailsActivity(), true);
                    if (isset($serviceDatas['info']) && isset($serviceDatas['extra']) && isset($serviceDatas['waypoints'])) { // test temporaire, le json a déjà été modifié !
                        $serviceDatas = $serviceDatas['extra'];
                    }
                    $trackingDatas = $importService->buildJsonToSave($user, $serviceDatas, $activityServiceName);
                    break;
                    
                case 'gpx':
                    // $gpx a été défini plus haut
                    $activityInfos = array(
                        'fileName'  => 'web/uploads/gpx/'.$gpx->getName()
                    );
                    $trackingDatas = $importService->buildJsonToSave($user, $activityInfos, 'gpx');

                    break;
                default:
                    throw new \Exception('Service name non pris en charge: '.$activityServiceName);
                    break;
            }
            
            
            if( $trackingDatas != null && is_array($trackingDatas) ) {
                $activity->setDistance($trackingDatas['info']['distance']);
                $activity->setDuration($trackingDatas['info']['timeDuration']);
                $activity->setTimeMoving($trackingDatas['info']['duration']);
                $activity->setTrackingDatas($trackingDatas);
            }
            
            $activity->setSource($activityServiceName);
            $em->persist($activity);
            
            $output->writeln($activity->getId()." ".$sportLabel.' '.$activityServiceName);
        }
        $em->flush();
    }
    
}
