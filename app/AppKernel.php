<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
		error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\DoctrineBundle\DoctrineBundle(),
            new Symfony\Bundle\DoctrineFixturesBundle\DoctrineFixturesBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new Ks\UserBundle\KsUserBundle(),
            new Ks\EventBundle\KsEventBundle(),
            new Ks\NotificationBundle\KsNotificationBundle(),
            new Ks\JobBundle\KsJobBundle(),
            new Ks\ActivityBundle\KsActivityBundle(),
            new Avalanche\Bundle\ImagineBundle\AvalancheImagineBundle(),
            new Ivory\GoogleMapBundle\IvoryGoogleMapBundle(),
            new FOS\FacebookBundle\FOSFacebookBundle(),
            //new FOS\GoogleBundle\FOSGoogleBundle(),
            //new FOS\TwitterBundle\FOSTwitterBundle(),
            new Vich\UploaderBundle\VichUploaderBundle(),
            new Ks\ClubBundle\KsClubBundle(),
            /*new Bundle\GitWikiBundle\GitWikiBundle(),-*/
            new Ks\ImageBundle\KsImageBundle(),
            new Ks\LeagueBundle\KsLeagueBundle(),
            new Ks\MailBundle\KsMailBundle(),
            new Ks\MessageBundle\KsMessageBundle(),
            new Ks\DashboardBundle\KsDashboardBundle(),
            new Ks\AgendaBundle\KsAgendaBundle(),
            new Ks\ContestBundle\KsContestBundle(),
            new Ks\TournamentBundle\KsTournamentBundle(),
            new Ks\SearchBundle\KsSearchBundle(),
            new Ks\TrophyBundle\KsTrophyBundle(),
            
            //new Lexik\Bundle\MaintenanceBundle\LexikMaintenanceBundle(),
            new Ks\CanvasDrawingBundle\KsCanvasDrawingBundle(),
            new Ks\EvolutionBundle\KsEvolutionBundle(),
            new Ks\ShopBundle\KsShopBundle(),
            new Ks\EquipmentBundle\KsEquipmentBundle(),
            new Ks\CoachingBundle\KsCoachingBundle(),
            new Ks\PaymentBundle\KsPaymentBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
