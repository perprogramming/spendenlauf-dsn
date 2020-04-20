<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Security\Authenticator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/login")
 */
class LoginController extends AbstractController
{
    /**
     * @Route("/", name="login_index")
     */
    public function index()
    {
        return $this->render('login/index.html.twig', [
            'controller_name' => 'LoginController',
        ]);
    }

    /**
     * @Route("/email/", name="login_email", methods={"POST"})
     */
    public function sendEmail(Authenticator $authenticator, MailerInterface $mailer, Request $request)
    {
        $submittedToken = $request->request->get('token');
        if (!$this->isCsrfTokenValid('login-email', $submittedToken)) {
            throw new AccessDeniedException();
        }

        $email = $request->request->get('email');
        $signature = $authenticator->signEmail($email);
        $url = $this->generateUrl('index', ['email' => $email, 'signature' => $signature], UrlGeneratorInterface::ABSOLUTE_URL);

        $message = new Email();
        $message->from("info@spendenlauf.perbernhardt.de")
            ->to($request->request->get('email'))
            ->subject("Einloggen beim Spendenlauf")
            ->text("Logge dich jetzt beim Spendenlauf ein: " . $url);
        
        $mailer->send($message);
        
        $this->addFlash("info", "Du solltest jetzt eine E-Mail mit einem Link zum einloggen haben!");
        return $this->redirectToRoute('login_index');
    }

}
