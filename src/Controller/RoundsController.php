<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Round;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class RoundsController extends AbstractController
{
    /**
     * @Route("/runde-eintragen/", name="add_round", methods={"POST"})
     */
    public function addRound(Request $request, TranslatorInterface $translator)
    {
        $submittedToken = $request->request->get('token');
        if (!$this->isCsrfTokenValid('add-round', $submittedToken)) {
            throw new AccessDeniedException();
        }

        $user = $this->getUser();

        $round = new Round($user);

        /** @var RoundRepository */
        $roundRepository = $this->getDoctrine()->getRepository(Round::class);
        $roundRepository->add($round);

        return $this->redirectToRoute('index', [$translator->trans('controller.rounds.parameter.round_was_added') => true]);
    }

}
