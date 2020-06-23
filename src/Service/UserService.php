<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserService {

    private const DEFAULT_USER_PERMISSIONS = ["CREATE_POST", "EDIT_POST", "DELETE_POST", "COMMENT_ON_POST"];

    /**
     * Entity Manager
     * 
     * @var EntityManagerInterface Entity Manager
     */
    private $entityManager;





    /**
     * Service Constructor
     * 
     * @param EntityManagerInterface Entity Manager
     */
    public function __construct(
        EntityManagerInterface $entityManager
    ) {
       $this->entityManager = $entityManager;
    }





    /**
     * Create User
     * 
     * @param UserPasswordEncoderInterface Password Encoder
     * @param string Username
     * @param string Password
     * 
     * @throws \Exception
     * 
     * @return User Created User
     */
    public function createUser(
        UserPasswordEncoderInterface $passwordEncoder,
        string $username,
        string $password
    ): User {
        if($this->isUsernameTaken($username)) {
            throw new \Exception("The username '$username' has been taken");
        }

        $user = new User();
        $user->setUsername($username);
        $user->setRoles([]);
        $user->setPassword($passwordEncoder->encodePassword($user, $password));
        $user->setPermissions(self::DEFAULT_USER_PERMISSIONS);
        $user->setIsBanned(false);
        $user->setCreatedDate(new \DateTime());

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }





    /**
     * Check if Username is taken
     * 
     * @param string Username
     * 
     * @return bool
     */
    private function isUsernameTaken(string $username): bool {
        $getUser = $this->entityManager->getRepository(User::class)->findOneBy([
            "username" => $username
        ]);

        return null !== $getUser;
    }





    /**
     * Manually authenticate user
     * 
     * @param User User
     * @param Request Request
     * @param TokenStorageInterface Token Storage
     */
    public function manualUserAuthentication(
        User $user,
        Request $request,
        TokenStorageInterface $tokenStorage
    ): void {
        $token = new UsernamePasswordToken(
            $user,
            null,
            "main",
            $user->getRoles()
        );

        $tokenStorage->setToken($token);

        $request->getSession()->set("_security_main", serialize($token));
    }





    /**
     * Mark user last seen
     * 
     * @param User User
     */
    public function markLastSeen(User $user): void {
        $user->setLastSeen(new \DateTime);

        $this->entityManager->flush();
    }





    /**
     * Make User Admin
     * 
     * @param Admin Admin
     * @param User User
     * 
     * @throws \Exception
     * 
     * @return bool
     */
    public function makeUserAdmin(
        User $admin,
        User $user
    ): bool {
        if(!$admin->isGranted("ROLE_SUPER_ADMIN")) {
            throw new \Exception("This action is for super admins only");
        }

        if($user->isGranted("ROLE_ADMIN")) {
            throw new \Exception("User is already an admin");
        }

        $user->setRoles(array_merge($user->getRoles(), ["ROLE_ADMIN"]));
        $this->entityManager->flush();

        $notificationService = new NotificationService($this->entityManager);
        try {
            $notificationService->createNotification($notificationService::UPGRADED_TO_ADMIN, $admin, $user);
        } catch(\Exception $e) {}

        return true;
    }





    /**
     * Remove User As Admin
     * 
     * @param Admin Admin
     * @param User User
     * 
     * @throws \Exception
     * 
     * @return bool
     */
    public function removeUserAsAdmin(
        User $admin,
        User $user
    ): bool {
        if(!$admin->isGranted("ROLE_SUPER_ADMIN")) {
            throw new \Exception("This action is for super admins only");
        }

        if(!$user->isGranted("ROLE_ADMIN")) {
            throw new \Exception("User is not an admin");
        }

        $roles = $user->getRoles();
        $adminKey = array_search("ROLE_ADMIN", $roles);
        unset($roles[$adminKey]);

        $user->setRoles($roles);
        $this->entityManager->flush();

        $notificationService = new NotificationService($this->entityManager);
        try {
            $notificationService->createNotification($notificationService::REMOVED_AS_ADMIN, $admin, $user);
        } catch(\Exception $e) {}

        return true;
    }
}