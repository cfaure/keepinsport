<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ks\UserBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Security\Core\SecurityContext;

use FOS\UserBundle\Controller\SecurityController as BaseController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


class SecurityController extends Controller
{
    
     /**
     * @Route("/", name="_login")
     * @Template
     */
    
    public function loginAction()
    {
        $em                 = $this->getDoctrine()->getEntityManager();

        $userRep            = $em->getRepository('KsUserBundle:User');
        
        //Récupération de la session
        $session = $this->get('session');
        $session->set('pageType', 'sportif');
        $session->set('page', 'login');
        
        //TODO meilleure gestion erreur quand l'email est déja en base 
        // "Pseudo ou mot de passe incorrect --> 
        // par exemple devrait afficher : "vous avez dejé un compte keepin avec cette adresse email"
        if($this->container->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')){
            $user = $this->get('security.context')->getToken()->getUser();
            /*
            if ($user->getIsAllowedPackPremium() || $user->getIsAllowedPackElite()) {
                return new RedirectResponse($this->container->get('router')->generate('ksAgenda_dashboard', array('id' => $user->getId())));
            }
            else {
                $route = 'ksActivity_activitiesList';
                $url = $this->container->get('router')->generate($route);
                return new RedirectResponse($url);
            }
            */
            return new RedirectResponse($this->container->get('router')->generate('ksAgenda_dashboard', array('id' => $user->getId())));
        }

        $request = $this->container->get('request');
        /* @var $request \Symfony\Component\HttpFoundation\Request */


        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } elseif (null !== $session && $session->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = '';
        }
    
        if ($error) {
            // TODO: this is a potential security risk (see http://trac.symfony-project.org/ticket/9523)
            $error = $error->getMessage();
        }
        
        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get(SecurityContext::LAST_USERNAME);
        
        $csrfToken = $this->container->get('form.csrf_provider')->generateCsrfToken('authenticate');
        
        $citations = array();
        $usersKs = array("clements", "adrien974", "stephane974", "quentin973", "moreljeanalain", "Sofi", "Domi76974");
        $texts = array();
        $texts[$usersKs[0]] = "Depuis que j'utilise keepinsport, je béneficie d'un suivi constant de mon entraînement et de ma progression. super outil qui permet à pascal blanc d'avoir un oeil attentif sur mon entraînement !";
        $texts[$usersKs[1]] = "Grâce à Keepinsport j'ai gagné en interactivité sur les plans d'entrainement que Pascal Blanc me fait. En cas de soucis le coach me modifie instantanément mon planning, et toutes mes séances sont détaillées ce qui me permet une progression continue. Vraiment un superbe outil au service de notre progression !";
        $texts[$usersKs[2]] = "Pour la préparation de La Diagonale des Fous 2014 j'ai décidé de prendre un coach . Ce n'est pas la motivation qui me manquait , ce n'est pas une perf que je recherchais , j'avais juste besoin d'un plan d'entraînement à suivre ... Pascal Blanc m'a apporté ce suivi , ses conseils , et grâce à Keepinsport j'ai de façon ludique 'enquillé' les séances , j'ai constaté ma progression , je me suis mesuré aux autres ... Et 4 mois après j'ai franchi cette putain de ligne au stade de la Redoute !";
        $texts[$usersKs[3]] = "Idéal pour observer sa progression et atteindre ses objectifs sportifs, merci Keepinsport pour sa plateforme interactive: géniale pour un coaching avec Pascal !";
        $texts[$usersKs[4]] = "Avec Keepinsport, je peux partager mes résussites et mes difficultés lors de mes entraînements !";
        $texts[$usersKs[5]] = "Pour ma part keepinsport m'as permis d'avoir un suivi plus direct avec Pascal, on peut échanger plus rapidement j'ai mon planning sur le site sur lequel je peux mettre des commentaires, on peut apporter une modification au plan si on a un empêchement et de son côté Pascal peut nous suivre de plus près et réadapter le plan selon notre emploi du temps c pratique :)";
        $texts[$usersKs[6]] = " Keepinsport est outil performant qui me permet de mesurer mon effort et ma performance et donne à mon coach Pascal Blanc toutes les données pour vérifier ma pratique correcte des activités planifiées. ";
        
        $citations = array();
        for ($i=0;$i<6;$i++) {
            $userToAffich = $userRep->findOneUser( array(
                "username" => $usersKs[$i]
            ));

            $citations[] = array(
                "user" => $userToAffich,
                "text" => $texts[$usersKs[$i]]
            );
        }

        return $this->container->get('templating')->renderResponse('FOSUserBundle:Security:login.html.'.$this->container->getParameter('fos_user.template.engine'), array(
            'last_username' => $lastUsername,
            'error'         => $error,
            'csrf_token'    => $csrfToken,
            'citations'     => $citations
        ));
    }
    
    /**
     * @Route("/google/login", name="_googleLogin")
     */
    public function googleLoginAction()
    {
        //var_dump("ici");
        return $this->redirect("fos_user_registration_register");
        if($this->container->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')){
            $route = 'ksActivity_activitiesList';
            $url = $this->container->get('router')->generate($route);

            return new RedirectResponse($url);
        }
        
        $request = $this->container->get('request');
        /* @var $request \Symfony\Component\HttpFoundation\Request */
        $session = $request->getSession();
        /* @var $session \Symfony\Component\HttpFoundation\Session */

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } elseif (null !== $session && $session->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = '';
        }
    

        if ($error) {
            // TODO: this is a potential security risk (see http://trac.symfony-project.org/ticket/9523)
            $error = $error->getMessage();
        }
      $googleApi = $this->get("fos_google.api");
      //return $this->redirect($googleApi->createAuthUrl());
      
      var_dump("error");
      /*return $this->container->get('templating')->renderResponse('FOSUserBundle:Security:login.html.'.$this->container->getParameter('fos_user.template.engine'), array(
            //'last_username' => $lastUsername,
            'error'         => $error,
            //'csrf_token' => $csrfToken,
            // 'facebookauth' => $facebookauth,
        ));*/
    }

    /*public function checkAction()
    {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }

    public function logoutAction()
    {
        throw new \RuntimeException('You must activate the logout in your security firewall configuration.');
    }*/  
}
