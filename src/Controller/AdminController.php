<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Round;
use App\Entity\User;
use App\Repository\RoundRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
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
     * @Route("/delete/{id}", name="admin_delete_round")
     */
    public function delete($id, Request $request)
    {
        $submittedToken = $request->request->get('token');
        if (!$this->isCsrfTokenValid('delete-round', $submittedToken)) {
            throw new AccessDeniedException();
        }

        $round = $this->getRoundRepository()->find($id);
        $this->getEntityManager()->remove($round);
        $this->getEntityManager()->flush();

        return $this->redirectToRoute('admin_index');
    }

    /**
     * @Route("/delete-all/{userId}", name="admin_delete_rounds")
     */
    public function deleteAll($userId, Request $request)
    {
        $submittedToken = $request->request->get('token');
        if (!$this->isCsrfTokenValid('delete-rounds', $submittedToken)) {
            throw new AccessDeniedException();
        }

        $rounds = $this->getRoundRepository()->findAllOf($userId);
        foreach ($rounds as $round) {
            $this->getEntityManager()->remove($round);
        }
        $this->getEntityManager()->flush();

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

    private function getEntityManager(): EntityManager
    {
        return $this->getDoctrine()->getManager();
    }
}
