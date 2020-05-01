<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Round;
use App\Entity\Settings;
use App\Entity\User;
use App\Repository\RoundRepository;
use App\Repository\SettingsRepository;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/", name="admin_index")
     */
    public function index(Request $request)
    {
        $userId = $request->query->getInt('userId') ?: null;
        $offset = max(0, $request->query->getInt('offset'));
        $limit = 10;

        $user = null;
        if ($userId) {
            $user = $this->getUserRepository()->find($userId);
            if (!$user) {
                return $this->redirectToRoute('admin_index', ['offset' => $offset]);
            }
        }

        $roundRepository = $this->getRoundRepository();
        $total = $roundRepository->countAll($userId);
        $rounds = $roundRepository->list($userId, $offset, $limit);

        $userIds = array_map(function($round) { return $round->getUserId(); }, $rounds);
        $users = $this->getUserRepository()->getUserMap($userIds);
        
        return $this->render('admin/index.html.twig', [
            'total' => $total,
            'user' => $user,
            'userId' => $userId,
            'users' => $users,
            'rounds' => $rounds,
            'offset' => $offset,
            'limit' => $limit
        ]);
    }

    /**
     * @Route("/einstellungen", name="admin_settings")
     */
    public function settings() {
        return $this->render('admin/settings.html.twig', [
            'settings' => $this->getSettingsRepository()->get()
        ]);
    }

    /**
     * @Route("/einstellungen/aktualisieren", name="admin_settings_update")
     */
    public function updateSettings(Request $request) {
        $submittedToken = $request->request->get('token');
        if (!$this->isCsrfTokenValid('update-settings', $submittedToken)) {
            throw new AccessDeniedException();
        }

        $this->getSettingsRepository()->set($request->request->get('settings'));

        return $this->redirectToRoute('admin_index');
    }

    /**
     * @Route("/loesche-runde/{id}", name="admin_delete_round")
     */
    public function delete($id, Request $request)
    {
        $submittedToken = $request->request->get('token');
        if (!$this->isCsrfTokenValid('delete-round', $submittedToken)) {
            throw new AccessDeniedException();
        }

        $roundRepository = $this->getRoundRepository();
        $round = $roundRepository->find($id);
        $roundRepository->delete($round);        

        return $this->redirectToRoute('admin_index');
    }

    /**
     * @Route("/loesche-alle-runden-von/{userId}", name="admin_delete_rounds")
     */
    public function deleteAll($userId, Request $request)
    {
        $submittedToken = $request->request->get('token');
        if (!$this->isCsrfTokenValid('delete-rounds', $submittedToken)) {
            throw new AccessDeniedException();
        }

        $roundRepository = $this->getRoundRepository();
        $rounds = $roundRepository->findAllOf($userId);
        $roundRepository->deleteMany($rounds);        

        return $this->redirectToRoute('admin_index');
    }

    private function getUserRepository(): UserRepository
    {
        return $this->getDoctrine()->getRepository(User::class);
    }

    private function getRoundRepository(): RoundRepository
    {
        return $this->getDoctrine()->getRepository(Round::class);
    }

    private function getSettingsRepository(): SettingsRepository
    {
        return $this->getDoctrine()->getRepository(Settings::class);
    }
}
