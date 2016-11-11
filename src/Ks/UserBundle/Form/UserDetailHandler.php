<?php 
namespace Ks\UserBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Ks\UserBundle\Entity\UserDetail;

class UserDetailHandler
{
    protected $form;
    protected $request;
    protected $em;
    protected $currentUser;

    public function __construct(Form $form, Request $request = null, EntityManager $em, /*Ks\UserBundle\Entity\User*/ $currentUser)
    {
        $this->form         = $form;
        $this->request      = $request;
        $this->em           = $em;
        $this->currentUser  = $currentUser;
    }

    public function process()
    {
        

        if( $this->request->getMethod() == 'POST' )
        {
            $this->form->bindRequest($this->request);

            if( $this->form->isValid() )
            {
                $entity = $this->form->getData();
                $idUser = $this->currentUser->getId();
                $user = $this->em->getRepository('KsUserBundle:User')->find($idUser);
                $params = $this->request->request->all();
                
                if(!empty($params["username"])){
                    if(preg_match("/^[a-z0-9]+$/",$params["username"])) {
                        $user->setUsername($params["username"]);
                        $user->setUsernameCanonical($params["username"]);
                    } 
                }

                $user->setUserDetail($entity);
                
                $this->onSuccess($entity , $user);
                
                return true;
            }
        }

        return false;
    }
    
    public function onSuccess(UserDetail $userDetail , /*Ks\UserBundle\Entity\User*/ $user)
    {
        $this->em->persist($userDetail);
        $this->em->persist($user);
        $this->em->flush();

    }
    
    
    /*public function onSuccessEdit(UserDetail $userDetail)
    {
        $this->em->persist($userDetail);
        $this->em->flush();
    }*/

    
}