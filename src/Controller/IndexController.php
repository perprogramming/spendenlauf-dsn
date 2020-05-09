<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Round;
use App\Entity\User;
use App\Entity\Settings;
use App\Repository\RoundRepository;
use App\Repository\SettingsRepository;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(Request $request, TranslatorInterface $translator)
    {        
        $roundsRepository = $this->getRoundRepository();
     
        $myRounds = null;
        $myCurrentAmount = 0;
        /** @var User */
        if ($user = $this->getUser()) {
            $myRounds = $roundsRepository->countAll($user->getId());
            $myCurrentAmount = $myRounds * $user->getRewardPerRound();
        }

        return $this->render('index/index.html.twig', [
            'numberOfRounds' => $roundsRepository->countAll(),
            'myRounds' => $myRounds,           
            'myCurrentAmount' => $myCurrentAmount,
            'statistics' => $roundsRepository->getStatistics(),
            'roundWasAdded' => $request->query->getBoolean($translator->trans('controller.rounds.parameter.round_was_added'))
        ]);
    }

    /**
     * @Route("/einstellungen", name="user_settings")
     */
    public function settings() {
        /** @var User */
        $user = $this->getUser();
        return $this->render('index/settings.html.twig', [
            'reward_per_round' => $user->getRewardPerRound()
        ]);
    }

    /**
     * @Route("/einstellungen/aktualisieren", name="user_settings_update")
     */
    public function updateSettings(Request $request) {
        $submittedToken = $request->request->get('token');
        if (!$this->isCsrfTokenValid('update-user-settings', $submittedToken)) {
            throw new AccessDeniedException();
        }

        /** @var User */
        $user = $this->getUser();
        $user->setRewardPerRound($request->request->getInt('reward_per_round'));
        $this->getUserRepository()->update($user);

        return $this->redirectToRoute('index');
    }

    /**
     * @Route("/ausloggen/", name="logout")
     */
    public function logout()
    {}

    private function getRoundRepository(): RoundRepository
    {
        return $this->getDoctrine()->getRepository(Round::class);
    }

    private function getUserRepository(): UserRepository
    {
        return $this->getDoctrine()->getRepository(User::class);
    }

    private function getSettingsRepository(): SettingsRepository
    {
        return $this->getDoctrine()->getRepository(Settings::class);
    }
}
