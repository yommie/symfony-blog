<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/{page}", name="home", requirements={"page"="\d+"})
     */
    public function index(
        Request $request,
        int $page = 1,
        ArticleRepository $articleRepository
    ): Response {
        $user = null;
        $params = [];

        if($this->getUser()) {
            $filterSubscriptions = $request->query->get("filter_subscriptions", "false");
            if($filterSubscriptions === "true") {
                $user = $this->getUser();
                $params["filter_subscriptions"] = "true";
            }
        }

        $recordsPerPage = $this->getParameter("records_per_page");
        $articles = $articleRepository->fetchArticles($recordsPerPage, $page, null,false, false, $user);
        $pages = ceil($articles["totalMatched"] / $recordsPerPage);

        return $this->render('home/index.html.twig', [
            "articles" => $articles,
            "page" => $page,
            "pages" => $pages,
            "params" => $params
        ]);
    }
}
