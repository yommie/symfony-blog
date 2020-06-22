<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/{page}", name="home", requirements={"page"="\d+"})
     */
    public function index(
        int $page = 1,
        ArticleRepository $articleRepository
    ): Response {
        $recordsPerPage = $this->getParameter("records_per_page");
        $articles = $articleRepository->fetchArticles($recordsPerPage, $page);
        $pages = ceil($articles["totalMatched"] / $recordsPerPage);

        return $this->render('home/index.html.twig', [
            "articles" => $articles,
            "page" => $page,
            "pages" => $pages
        ]);
    }
}
