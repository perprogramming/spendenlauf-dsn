<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Round;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        return $this->render('index/index.html.twig', [
            'numberOfRounds' => $this->getDoctrine()->getRepository(Round::class)->countAll()
        ]);
    }

    /**
     * @Route("/ausloggen/", name="logout")
     */
    public function logout()
    {}
}
