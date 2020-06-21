<?php

namespace App\EventListener;

use App\Entity\User;
use App\Service\UserService;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

class AuthenticationEventListener {

    private $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    public function initialize(AuthenticationEvent $event) {
        $user = $event->getAuthenticationToken()->getUser();
        
        if($user instanceof User) {
            $this->userService->markLastSeen($user);
        }
    }
}