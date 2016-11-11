<?php 
namespace Ks\UserBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;

class ProfileInformationsHandler
{
    protected $form;
    protected $request;
    protected $em;
    protected $container;

    public function __construct(Form $form, Request $request, EntityManager $em, $container)
    {
        $this->form         = $form;
        $this->request      = $request;
        $this->em           = $em;
        $this->container    = $container;
    }

    public function process()
    {
        if( $this->request->getMethod() == 'POST' )
        {
            $user = $this->form->getData();
            $parameters       = $this->request->request->all();
            
            $this->form->bindRequest( $this->request );
            
            if( $this->form->isValid() )
            {
                $errors = array();
                $isValid = true;
                
                //Vérification sur le changement de pseudo
                $findUser = $this->em->getRepository('KsUserBundle:User')->findOneByUsername( $parameters["username"] );

                $exist = false;
                if(is_object($findUser) && $findUser->getId() != $user->getId()){
                    $exist = true;
                }
                
                if( $exist ) {
                    $isValid = false;
                    $errors["username"] = "Ce nom d'utilisateur existe déjà";
                }
                
                //FMO Trace sur modif du parrain
                if( isset( $parameters["ProfileInformationsType"]["godFather"] ) && ! empty( $parameters["ProfileInformationsType"]["godFather"] ) ) {
                    $userAction = new \Ks\ActivityBundle\Entity\UserAction();
                    $userAction->setUserId($user->getId());
                    $userAction->setAction('Update godFather : '.$parameters["ProfileInformationsType"]["godFather"]);
                    $userAction->setType('godFather');
                    $userAction->setResult('OK');
                    $userAction->setError(null);
                    $userAction->setDoneAt(new \DateTime("now"));

                    $this->em->persist($userAction);
                    $this->em->flush();
                }
                
                //Vérification du changement de mot de passe
                $encoder = $this->container
                    ->get('security.encoder_factory')
                    ->getEncoder($user);
                
                if( isset( $parameters["oldPassword"] ) && ! empty( $parameters["oldPassword"] ) ) {
                    if( $user->getPassword() != $encoder->encodePassword( $parameters["oldPassword"] , $user->getSalt())) {
                        $isValid = false;
                        $errors["password"] = "L'ancien mot de passe n'est pas correct !";
                    } else {
                        if( ( isset( $parameters["newPassword"] ) &&  !empty( $parameters["newPassword"] ) ) || ( isset( $parameters["newPasswordRepeat"] ) &&  !empty( $parameters["newPasswordRepeat"] )) ) {
                            if( $parameters["newPassword"] != $parameters["newPasswordRepeat"]) {
                                $isValid = false;
                                $errors["password"] = "Les 2 nouveaux mots de passe ne sont pas similaires !";
                            }
                        } else {
                            $isValid = false;
                            $errors["password"] = "Le nouveau mot de passe ne peut pas être vide !";
                        }
                    }
                }
                
                if( $isValid ) {
                    $responseDatas = $this->onSuccess($user, $parameters);  
                } else {
                    $responseDatas = $this->onError( $errors ); 
                }
                
            } else {
                $errors = \Form\FormValidator::getErrorMessages($this->form);
                $responseDatas = $this->onError($errors);  
            }
        } else {
            $responseDatas = $this->onIsNotPostRequest(); 
        }

        return $responseDatas;
    }
    
    public function onSuccess(\Ks\UserBundle\Entity\User $user, $parameters)
    {
        if(!empty($parameters["username"])){
            if(preg_match("/^[a-z0-9]+$/",$parameters["username"])) {
                $user->setUsername($parameters["username"]);
                $user->setUsernameCanonical($parameters["username"]);
            } 
        }
        
        if(isset( $parameters["newPassword"] ) && !empty( $parameters["newPassword"] ) ) {
            $encoder = $this->container
                ->get('security.encoder_factory')
                ->getEncoder($user);
            
            $newPassword = $encoder->encodePassword( $parameters["newPassword"] , $user->getSalt() );
            $user->setPassword( $newPassword );
        }
       
        //Par default, l'utilisateur recevra les mails quotidiens
        //$user->GetUserDetail()->setReceivesDailyEmail( true );

        $userManager = $this->container->get('fos_user.user_manager');
        $userManager->updateUser($user, true);
        
        //$this->em->persist($user);
        //$this->em->flush();
        
        $userDetail = $user->getUserDetail();
        
        $responseDatas = array(
            'response' => 1,
            'user' => $user
        ); 
        
        return $responseDatas;
        
        
    }
    
    public function onError($errors)
    {
        $responseDatas = array(
            'response'   => -1,
            'errorMessage'      => "Le formulaire n'est pas valide.",
            'errors'            => $errors
        ); 
        
        return $responseDatas;
    }
    
    public function onIsNotPostpRequest()
    {
        $responseDatas = array(
            'response'   => -1,
            'errorMessage'      => "La requête n'est pas une requête ajax."
        ); 
        
        return $responseDatas;
    }
    
    
    /*public function onSuccessEdit(UserDetail $userDetail)
    {
        $this->em->persist($userDetail);
        $this->em->flush();
    }*/

    
}