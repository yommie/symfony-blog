<?php

namespace App\Controller\Subscription;

use App\Repository\ArticleRepository;
use App\Repository\SubscriptionRepository;
use App\Repository\UserRepository;
use App\Service\SubscriptionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SubscriptionController extends AbstractController
{
    /**
     * @Route("/app/subscriptions/subscribe/{username}", name="subscribeAuthor")
     */
    public function subscribeAuthor(
        string $username,
        UserRepository $userRepository,
        SubscriptionService $subscriptionService
    ): Response {
        $author = $userRepository->findOneBy([
            "username" => $username
        ]);

        if(null === $author) {
            // TODO: Return a 404 error response
            $this->addFlash("home-error", "Author not found");
            return $this->redirectToRoute("home");
        }

        try {
            $subscriptionService->subscribeToAuthor($this->getUser(), $author);
        } catch(\Exception $e) {
            $this->addFlash("author-error", $e->getMessage());
        }

        return $this->redirectToRoute("viewAuthor", ["username" => $username]);
    }





    /**
     * @Route("/app/subscriptions/unsubscribe/{username}", name="unsubscribeAuthor")
     */
    public function unsubscribeAuthor(
        string $username,
        UserRepository $userRepository,
        SubscriptionService $subscriptionService
    ): Response {
        $author = $userRepository->findOneBy([
            "username" => $username
        ]);

        if(null === $author) {
            // TODO: Return a 404 error response
            $this->addFlash("home-error", "Author not found");
            return $this->redirectToRoute("home");
        }

        try {
            $subscriptionService->unsubscribeToAuthor($this->getUser(), $author);
            $this->addFlash("subscriptions-success", "Successfully unsubscribed from " . $author->getUsername());
        } catch(\Exception $e) {
            $this->addFlash("subscriptions-error", $e->getMessage());
        }

        return $this->redirectToRoute("subscriptions");
    }





    /**
     * @Route("/app/subscriptions/{page}", name="subscriptions", requirements={"page"="\d+"})
     */
    public function subscriptions(
        int $page = 1,
        SubscriptionRepository $subscriptionRepository
    ): Response {
        $recordsPerPage = $this->getParameter("records_per_page");
        $subscriptions = $subscriptionRepository->fetchSubscriptions($recordsPerPage, $page, $this->getUser());
        $pages = ceil($subscriptions["totalMatched"] / $recordsPerPage);

        return $this->render('subscription/index.html.twig', [
            "subscriptions" => $subscriptions,
            "page" => $page,
            "pages" => $pages
        ]);
    }
}
