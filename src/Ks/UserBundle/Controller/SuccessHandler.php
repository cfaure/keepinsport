<?php
namespace Ks\UserBundle\Controller;
use Symfony\Component\Routing\RouterInterface,
    Symfony\Component\HttpFoundation\RedirectResponse,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface,
    Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
 
class SuccessHandler implements AuthenticationSuccessHandlerInterface
{
    protected $router;
 
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }
 
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
    	if($request->isXmlHttpRequest()){
			$response = new Response(json_encode(array(
				'success'=> 1,
			)));
			$response->headers->set('Content-Type', 'application/json');
			return $response;
		}else{
			
                    return new RedirectResponse($this->router->generate('fos_user_security_login'));
    	}
    }
}
?>