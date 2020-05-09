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
use Symfony\Contracts\Translation\TranslatorInterface;

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
    public function sendEmail(Authenticator $authenticator, MailerInterface $mailer, Request $request, TranslatorInterface $translator)
    {
        $submittedToken = $request->request->get('token');
        if (!$this->isCsrfTokenValid('login-email', $submittedToken)) {
            throw new AccessDeniedException();
        }

        $email = $request->request->get('email');

        if (!preg_match('([^@]+@(germanschool.co.ke|bernhardt.ws))', $email)) {
            $this->addFlash('error', $translator->trans('controller.login.messages.invalidEmail'));
            return $this->redirectToRoute('login_index');
        }

        $signature = $authenticator->signEmail($email);
        $url = $this->generateUrl('index', ['email' => $email, 'signature' => $signature], UrlGeneratorInterface::ABSOLUTE_URL);

        $message = new Email();
        $message->from($this->getParameter('mail_sender'))
            ->to($request->request->get('email'))
            ->subject($translator->trans('controller.login.email.subject'))
            ->text($translator->trans('controller.login.email.text', ['%url%' => $url]));
        
        $mailer->send($message);
        
        $this->addFlash("info", $translator->trans('controller.login.messages.success'));
        return $this->redirectToRoute('login_index');
    }
}
