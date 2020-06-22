<?php

namespace App\Controller\Admin;

use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DashboardController extends AbstractController
{
    /**
     * @Route("/dashboard", name="adminDashboard")
     */
    public function adminDashboard(
        UserRepository $userRepository,
        ArticleRepository $articleRepository
    ): Response {
        $userCount = $userRepository->getUserCount();
        $bannedUsers = $userRepository->getUserCount(true);
        $articleCount = $articleRepository->getArticleCount();
        $bannedArticles = $articleRepository->getArticleCount(true);

        return $this->render('admin/dashboard.html.twig', [
            "usersCount" => $userCount,
            "bannedUsers" => $bannedUsers,
            "articleCount" => $articleCount,
            "bannedArticles" => $bannedArticles
        ]);
    }
}