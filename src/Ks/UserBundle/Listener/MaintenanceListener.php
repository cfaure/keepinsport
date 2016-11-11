<?php
/**
 * Created by PhpStorm.
 * User: clfau_000
 * Date: 11/06/14
 * Time: 00:10
 */

namespace Ks\UserBundle\Listener;


use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class MaintenanceListener
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $maintenanceUntil   = $this->container->hasParameter('underMaintenanceUntil') ? $this->container->getParameter('underMaintenanceUntil') : false;
        $maintenance        = $this->container->hasParameter('maintenance') ? $this->container->getParameter('maintenance') : false;

        $debug              = 0; // NOTE CF: test désactivé pour débug // in_array($this->container->get('kernel')->getEnvironment(), array('test', 'dev'));

        if ($maintenance && !$debug) {
            $engine     = $this->container->get('templating');
            $content    = $engine->render('::maintenance.html.twig', array('maintenanceUntil' => $maintenanceUntil));
            $event->setResponse(new Response($content, 503));
            $event->stopPropagation();
        }
    }
} 