<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class Authenticator extends AbstractGuardAuthenticator
{
    private $em;
    private $kernelSecret;

    public function __construct(EntityManagerInterface $em, string $kernelSecret)
    {
        $this->em = $em;
        $this->kernelSecret = $kernelSecret;
    }

    public function supports(Request $request)
    {
        return $request->query->has('email') && $request->query->has('signature');
    }

    public function getCredentials(Request $request)
    {
        return $request->query->all();
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $email = $credentials['email'];

        $user = $this->em->getRepository(User::class)
            ->findOneBy(['email' => $email]);
        
        if (!$user) {
            $user = new User($email);
            $this->em->persist($user);
            $this->em->flush();
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return $credentials['signature'] == $this->signEmail($credentials['email']);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return new RedirectResponse("/");
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return $this->start($request, $exception);
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse("/einloggen/");
    }

    public function supportsRememberMe()
    {
        return false;
    }

    public function signEmail(string $email)
    {
        return hash('md5', $email . $this->kernelSecret);
    }
}
