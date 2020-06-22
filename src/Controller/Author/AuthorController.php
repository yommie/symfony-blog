<?php

namespace App\Controller\Author;

use App\Repository\ArticleRepository;
use App\Repository\SubscriptionRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthorController extends AbstractController
{
    /**
     * @Route("/author/{username}/{page}", name="viewAuthor", requirements={"page"="\d+"})
     */
    public function viewAuthor(
        string $username,
        int $page = 1,
        UserRepository $userRepository,
        ArticleRepository $articleRepository,
        SubscriptionRepository $subscriptionRepository
    ): Response {
        $author = $userRepository->findOneBy([
            "username" => $username
        ]);

        if(null === $author) {
            // TODO: Return a 404 error response
            $this->addFlash("home-error", "Author not found");
            return $this->redirectToRoute("home");
        }
        
        $recordsPerPage = $this->getParameter("records_per_page");

        $shouldFetchDraft = ($this->getUser() === $author) ? true : false;
        $shouldFetchBanned = ($this->getUser() === $author) ? true : false;

        $articles = $articleRepository->fetchArticles($recordsPerPage, $page, $author, $shouldFetchDraft, $shouldFetchBanned);
        $pages = ceil($articles["totalMatched"] / $recordsPerPage);

        return $this->render('author/view.html.twig', [
            'author' => $author,
            "articles" => $articles,
            "page" => $page,
            "pages" => $pages,
            "subscriptionRepository" => $subscriptionRepository
        ]);
    }
}
