<?php

namespace App\Controller\Admin;

use App\Repository\SubscriptionRepository;
use App\Repository\UserRepository;
use App\Service\NotificationService;
use App\Service\UserService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RolesController extends AbstractController
{
    /**
     * @Route("/{username}/make-admin", name="adminMakeAdmin")
     */
    public function adminMakeAdmin(
        string $username,
        UserService $userService,
        UserRepository $userRepository
    ): Response {
        $user = $userRepository->findOneBy([
            "username" => $username
        ]);

        if(null === $user) {
            // TODO: Return a 404 error response
            $this->addFlash("authors-error", "Author not found");
            return $this->redirectToRoute("authors");
        }

        $this->denyAccessUnlessGranted("ROLE_SUPER_ADMIN", $this->getUser());

        try {
            $userService->makeUserAdmin($this->getUser(), $user);
        } catch(\Exception $e) {
            $this->addFlash("author-error", $e->getMessage());
        }

        return $this->redirectToRoute("viewAuthor", ["username" => $username]);
    }






    /**
     * @Route("/{username}/remove-as-admin", name="adminRemoveAdmin")
     */
    public function adminRemoveAdmin(
        string $username,
        UserService $userService,
        UserRepository $userRepository
    ): Response {
        $user = $userRepository->findOneBy([
            "username" => $username
        ]);

        if(null === $user) {
            // TODO: Return a 404 error response
            $this->addFlash("authors-error", "Author not found");
            return $this->redirectToRoute("authors");
        }

        $this->denyAccessUnlessGranted("ROLE_SUPER_ADMIN", $this->getUser());

        try {
            $userService->removeUserAsAdmin($this->getUser(), $user);
        } catch(\Exception $e) {
            $this->addFlash("author-error", $e->getMessage());
        }

        return $this->redirectToRoute("viewAuthor", ["username" => $username]);
    }






    /**
     * @Route("/{username}/ban", name="adminBanUser")
     */
    public function adminBanUser(
        string $username,
        UserRepository $userRepository,
        NotificationService $notificationService
    ): Response {
        $user = $userRepository->findOneBy([
            "username" => $username
        ]);

        if(null === $user) {
            // TODO: Return a 404 error response
            $this->addFlash("authors-error", "Author not found");
            return $this->redirectToRoute("authors");
        }

        $this->denyAccessUnlessGranted("CAN_BAN_USER", $this->getUser());

        $user->setIsBanned(true);

        try {
            $notificationService->createNotification($notificationService::USER_BANNED, $this->getUser(), $user);
        } catch(\Exception $e) {
            $this->addFlash("author-error", $e->getMessage());
        }

        return $this->redirectToRoute("viewAuthor", ["username" => $username]);
    }






    /**
     * @Route("/{username}/unban", name="adminUnbanUser")
     */
    public function adminUnbanUser(
        string $username,
        UserRepository $userRepository,
        NotificationService $notificationService
    ): Response {
        $user = $userRepository->findOneBy([
            "username" => $username
        ]);

        if(null === $user) {
            // TODO: Return a 404 error response
            $this->addFlash("authors-error", "Author not found");
            return $this->redirectToRoute("authors");
        }

        $this->denyAccessUnlessGranted("CAN_BAN_USER", $this->getUser());

        $user->setIsBanned(false);

        try {
            $notificationService->createNotification($notificationService::USER_UNBANNED, $this->getUser(), $user);
        } catch(\Exception $e) {
            $this->addFlash("author-error", $e->getMessage());
        }

        return $this->redirectToRoute("viewAuthor", ["username" => $username]);
    }






    /**
     * @Route("/{username}/permissions", name="adminPermissions")
     */
    public function adminPermissions(
        string $username,
        UserRepository $userRepository,
        SubscriptionRepository $subscriptionRepository
    ): Response {
        $user = $userRepository->findOneBy([
            "username" => $username
        ]);

        if(null === $user) {
            // TODO: Return a 404 error response
            $this->addFlash("authors-error", "Author not found");
            return $this->redirectToRoute("authors");
        }

        $this->denyAccessUnlessGranted("ROLE_SUPER_ADMIN");

        $user->setIsBanned(false);

        return $this->render('author/permissions.html.twig', [
            'author' => $user,
            "subscriptionRepository" => $subscriptionRepository
        ]);
    }
}