<?php

namespace Ks\MailBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction($name)
    {
        return $this->render('KsMailBundle:Default:index.html.twig', array('name' => $name));
    }
}
