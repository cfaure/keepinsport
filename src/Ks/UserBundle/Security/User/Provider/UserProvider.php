<?php
 
namespace Ks\UserBundle\Security\User\Provider; 
 
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
 
class UserProvider implements UserProviderInterface
{
    private $userManager;
 
    public function __construct(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }
 
    //On surcharge cette méthode qui se trouve dans FOS/UserBundle/Model/UserManager.php
    public function loadUserByUsername($username)
    {
        $user = $this->userManager->findUserByUsernameOrEmail($username);//On cherche dans les deux
 
        if (!$user) {
            throw new UsernameNotFoundException(sprintf('No user with name "%s" was found.', $username));
        }
 
        return $user;
    }
 
    public function refreshUser(UserInterface $user)
    {
        return $this->userManager->refreshUser($user);
    }
 
    public function supportsClass($class)
    {
        return $this->userManager->supportsClass($class);
    }
}