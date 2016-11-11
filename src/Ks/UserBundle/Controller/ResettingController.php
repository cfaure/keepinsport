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

use Symfony\Component\HttpFoundation\RedirectResponse;
use FOS\UserBundle\Controller\ResettingController as BaseController;
use FOS\UserBundle\Model\UserInterface;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller managing the resetting of the password
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 * @author Christophe Coevoet <stof@notk.org>
 */
class ResettingController extends BaseController
{
    const SESSION_EMAIL = 'fos_user_send_resetting_email/email';

    /**
     * Request reset user password: submit form and send email
     */
    public function sendEmailAction()
    {
        $username = $this->container->get('request')->request->get('username');

        $user = $this->container->get('fos_user.user_manager')->findUserByUsernameOrEmail($username);

        if (null === $user) {
            return $this->container->get('templating')->renderResponse('FOSUserBundle:Resetting:request.html.'.$this->getEngine(), array('invalid_username' => $username));
        }

        /*if ($user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
            return $this->container->get('templating')->renderResponse('FOSUserBundle:Resetting:passwordAlreadyRequested.html.'.$this->getEngine());
        }*/

        $user->generateConfirmationToken();
        $this->container->get('session')->set(static::SESSION_EMAIL, $user->getEmail());
        //$this->container->get('fos_user.mailer')->sendResettingEmailMessage($user);
        
        $host       = $this->container->getParameter('host');
        $pathWeb    = $this->container->getParameter('path_web');
        $mailer     = $this->container->get('mailer');
        
        //Envoi d'un mail à l'utilisateur pour lui signaler le traitement de son problème   
        $url = $this->container->get('router')->generate('fos_user_resetting_reset', array('token' => $user->getConfirmationToken()), true);
        $contentMail = $this->container
            ->get('templating')
            ->render(
                'KsUserBundle:Resetting:_resetting_password_mail.html.twig',
                array(
                    'user' => $user,
                    'confirmationUrl' => $url
                ),
                'text/html'
            );
        $body = $this->container->get('templating')->render('KsUserBundle:User:template_mail.html.twig', 
            array(
                'host'      => $host,
                'pathWeb'   => $pathWeb,
                'content'   => $contentMail,
                'user'      => is_object( $user ) ? $user : null
            ), 
        'text/html');

        $message = \Swift_Message::newInstance()
            ->setContentType('text/html')
            ->setSubject("Feedback bien reçu, merci.")
            ->setFrom("contact@keepinsport.com")
            ->setTo($user->getEmail())
            ->setBody($body);

        $mailer->getTransport()->start();
        $mailer->send($message);
        $mailer->getTransport()->stop();
        
        $user->setPasswordRequestedAt(new \DateTime());
        $this->container->get('fos_user.user_manager')->updateUser($user);

        return new RedirectResponse($this->container->get('router')->generate('fos_user_resetting_check_email'));
    }
    
    /**
     * Reset user password
     */
    public function resetAction($token)
    {
        $user = $this->container->get('fos_user.user_manager')->findUserByConfirmationToken($token);

        if (null === $user) {
            $this->container->get('session')->setFlash('alert alert-error', 'users.profil_reinit_fail');
            $this->container->get('session')->setFlash('alert alert-info', 'users.profil_reinit_info');
            //Redirection vers la page de réinitialisation du mot de passe + msg flash 
            return new RedirectResponse($this->container->get('router')->generate('fos_user_resetting_request'));
        }

        if (!$user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
            return new RedirectResponse($this->container->get('router')->generate('fos_user_resetting_request'));
        }

        $form = $this->container->get('fos_user.resetting.form');
        $formHandler = $this->container->get('fos_user.resetting.form.handler');
        $process = $formHandler->process($user);

        if ($process) {
            $this->authenticateUser($user);

            $this->setFlash('alert alert-success', 'resetting.flash.success');

            return new RedirectResponse($this->getRedirectionUrl($user));
        }

        return $this->container->get('templating')->renderResponse('FOSUserBundle:Resetting:reset.html.'.$this->getEngine(), array(
            'token' => $token,
            'form' => $form->createView(),
            'theme' => $this->container->getParameter('fos_user.template.theme'),
        ));
    }
    
    /**
     * Generate the redirection url when the resetting is completed.
     *
     * @param \FOS\UserBundle\Model\UserInterface $user
     *
     * @return string
     */
    protected function getRedirectionUrl(UserInterface $user)
    {
        //return $this->container->get('router')->generate('ks_user_public_profile', array('username' => $user->getUsername()));
        return $this->container->get('router')->generate('ksActivity_activitiesList');
    }

   
}
