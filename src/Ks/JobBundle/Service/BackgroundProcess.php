<?php

namespace Ks\JobBundle\Service;

use Symfony\Bundle\DoctrineBundle\Registry;

/**
 * Description of BackgroundProcess
 *
 * @author Clem
 */
class BackgroundProcess
{
    protected $_doctrine;
    
    /**
     *
     */
    public function __construct(Registry $doctrine)
    {
        $this->_doctrine = $doctrine;
    }
    
    /**
     * FIXME: not working....
     * 
     * @param string $consoleCommand
     * @return boolean 
     */
    public function execute($consoleCommand)
    {
        $call   = 'php console '.$consoleCommand;
        $cwd    = @getcwd().'/../app';
        @chdir($cwd);
//        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
//            $wshShell = new \COM("WScript.Shell");
//            $wshShell->CurrentDirectory = str_replace('/', '\\', $cwd);
//            $wshShell->Run($call, 0, false);
//            
//        } else {
//            exec($call . " > /dev/null 2>&1 &");
//        }

        if ($this->isWindows()) {    
            pclose(popen('start /B '.$call, 'r'));
        } else {
            pclose(popen($call.' > /dev/null &', 'r'));
        }
        
        return true;
    }
    
    /**
     * Is Windows
     *
     * Tells if we are running on Windows Platform
     */
    protected final function isWindows()
    {
        if (PHP_OS == 'WINNT' || PHP_OS == 'WIN32') {
            return true;
        }
        
        return false;
    }
    
    /**
     *
     * @param string $service
     * @param string $callback
     * @param array $params
     * @throws PDOException 
     */
    public function queueJob($service, $callback, array $params = array())
    {
        $job = new \Ks\JobBundle\Entity\Job();
        $job->setService($service);
        $job->setCallback($callback);
        $job->setParams(serialize($params));
        $em = $this->_doctrine->getEntityManager();
        try {
            $em->persist($job);
            $em->flush();
        } catch(\PDOException $e) {
            if ($e->getCode() != 23000) { // unique integrity constraint...
                throw $e;
            }
        }
    }
    
    /**
     *
     * @param type $service
     * @param type $callback
     * @param type $params 
     */
    public function runJob($service, $callback, $params)
    {
        return $service->$callback(unserialize($params));
    }
}
