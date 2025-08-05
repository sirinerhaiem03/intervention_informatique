<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $user = $token->getUser();

        if ($user instanceof \App\Entity\Utilisateur) {
            return match ($user->getRole()->value) {
                'Responsable' => new RedirectResponse($this->router->generate('admin_home')),
                'Technicien'  => new RedirectResponse($this->router->generate('home_technicien')),
                'Agent'       => new RedirectResponse($this->router->generate('home')),

            };
        }

        return new RedirectResponse($this->router->generate('app_login'));
    }
}
