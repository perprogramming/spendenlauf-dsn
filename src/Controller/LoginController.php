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
 * @Route("/einloggen")
 */
class LoginController extends AbstractController
{
    /**
     * @Route("/", name="login_index")
     */
    public function index()
    {
        if ($user = $this->getUser()) {
            return $this->redirectToRoute('index');
        }
        return $this->render('login/index.html.twig');
    }

    /**
     * @Route("/email-versenden/", name="login_email", methods={"POST"})
     */
    public function sendEmail(Authenticator $authenticator, MailerInterface $mailer, Request $request)
    {
        $submittedToken = $request->request->get('token');
        if (!$this->isCsrfTokenValid('login-email', $submittedToken)) {
            throw new AccessDeniedException();
        }

        $email = $request->request->get('email');

        if (!preg_match('([^@]+@(germanschool.co.ke|bernhardt.ws))', $email)) {
            $this->addFlash('error', "Bitte verwende nur deine schulische E-Mail-Adresse (z.B. max.mustermann@germanschool.co.ke)!");
            return $this->redirectToRoute('login_index');
        }

        $signature = $authenticator->signEmail($email);
        $url = $this->generateUrl('index', ['email' => $email, 'signature' => $signature], UrlGeneratorInterface::ABSOLUTE_URL);

        $message = new Email();
        $message->from("info@spendenlauf.perbernhardt.de")
            ->to($request->request->get('email'))
            ->subject("Einloggen beim Spendenlauf")
            ->text("Hallo!\n\nLogge dich jetzt beim Spendenlauf ein, indem du auf folgenden Link klickst:\n\n$url\n\nViel Erfolg!");
        
        $mailer->send($message);
        
        $this->addFlash("info", "Die E-Mail mit dem Link zum Einloggen wurde erfolgreich versendet!");
        return $this->redirectToRoute('login_index');
    }
}
