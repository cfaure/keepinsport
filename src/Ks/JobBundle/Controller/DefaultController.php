<?php

namespace Ks\JobBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/hello/{name}")
     * @Template()
     */
    public function indexAction($name)
    {
        return array('name' => $name);
    }
    
    /**
     * @Route("/run/{id}")
     * @Template()
     * 
     */
    public function runAction($id)
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $job        = $em->getRepository('KsJobBundle:Job')
                        ->findOneById($id);
        if (!$job) {
            throw new Exception('Job not found: '.$id);
        }
        $this->get('ks_job.background_process')->runJob(
            $this->get($job->getService()),
            $job->getCallback(),
            $job->getParams()
        );
    }
}
