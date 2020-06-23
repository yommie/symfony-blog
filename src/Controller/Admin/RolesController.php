<?php

namespace App\Controller\Admin;

use App\Service\UserService;
use App\Repository\UserRepository;
use App\Service\NotificationService;
use App\Repository\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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

        $this->denyAccessUnlessGranted("ROLE_SUPER_ADMIN");

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

        $this->denyAccessUnlessGranted("ROLE_SUPER_ADMIN");

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

        $this->denyAccessUnlessGranted("CAN_BAN_USER", $user);

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

        $this->denyAccessUnlessGranted("CAN_BAN_USER", $user);

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
        Request $request,
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

        $this->denyAccessUnlessGranted("CAN_SET_PERMISSIONS", $user);

        $permissions = ["CREATE_POST", "EDIT_POST", "DELETE_POST", "COMMENT_ON_POST"];
        $adminPermissions = ["CAN_BAN_POST", "CAN_BAN_USER", "CAN_SET_PERMISSIONS"];

        if($user->isGranted("ROLE_ADMIN")) {
            $permissions = array_merge($permissions, $adminPermissions);
        }

        $choices = [];
        foreach($permissions as $permission) {
            $choices[ucwords(strtolower(str_replace("_", " ", $permission)))] = $permission;
        }

        $permissionsForm = $this->createFormBuilder()
            ->add("permissions", ChoiceType::class, [
                "label" => "Permissions",
                "choices" => $choices,
                "multiple" => true
            ])
            ->getForm();

        $permissionsForm->handleRequest($request);

        if($permissionsForm->isSubmitted()) {
            if($permissionsForm->isValid()) {
                $data = $permissionsForm->getData();

                $user->setPermissions($data["permissions"]);
                $this->getDoctrine()->getManager()->flush();

                $this->addFlash("permissions-success", "Permissions successfully updated");
            }
        } else {
            $permissionsForm->get("permissions")->setData($user->getPermissions());
        }

        return $this->render('author/permissions.html.twig', [
            'author' => $user,
            "subscriptionRepository" => $subscriptionRepository,
            "permissionsForm" => $permissionsForm->createView()
        ]);
    }
}