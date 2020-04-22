<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Round;
use App\Repository\RoundRepository;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        /** @var RoundsRepository */
        $roundsRepository = $this->getDoctrine()->getRepository(Round::class);
        $myRounds = null;

        if ($user = $this->getUser()) {
            $myRounds = $roundsRepository->countAll($user->getId());
        }

        return $this->render('index/index.html.twig', [
            'numberOfRounds' => $roundsRepository->countAll(),
            'myRounds' => $myRounds
        ]);
    }

    /**
     * @Route("/ausloggen/", name="logout")
     */
    public function logout()
    {}
}
