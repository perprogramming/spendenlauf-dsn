<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Round;
use Symfony\Component\HttpFoundation\Request;

class RoundsController extends AbstractController
{
    /**
     * @Route("/runde-eintragen/", name="add_round", methods={"POST"})
     */
    public function addRound(Request $request)
    {
        $submittedToken = $request->request->get('token');
        if (!$this->isCsrfTokenValid('add-round', $submittedToken)) {
            throw new AccessDeniedException();
        }

        $user = $this->getUser();

        $round = new Round($user);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($round);
        $entityManager->flush();

        return $this->redirectToRoute('index');
    }

}
