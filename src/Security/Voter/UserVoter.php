<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserVoter extends Voter
{
    private const BAN_USER = "CAN_BAN_USER";
    private const SET_PERMISSIONS = "CAN_SET_PERMISSIONS";

    private const SUPPORTED = [self::BAN_USER, self::SET_PERMISSIONS];

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, self::SUPPORTED) && $subject instanceof User;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        
        if(!$user instanceof UserInterface) {
            return false;
        }

        switch($attribute) {
            case self::BAN_USER:
                return $this->canBanUser($subject, $user);
            case self::SET_PERMISSIONS:
                return $this->canSetPermissions($subject, $user);
        }

        return false;
    }

    private function canBanUser(User $user, User $admin) {
        if(!$this->security->isGranted("ROLE_ADMIN") || $user->isGranted("ROLE_SUPER_ADMIN")) {
            return false;
        }

        return $this->security->isGranted("ROLE_SUPER_ADMIN") || in_array(self::BAN_USER, $admin->getPermissions());
    }

    private function canSetPermissions(User $user, User $admin) {
        if(!$this->security->isGranted("ROLE_ADMIN") || $user->isGranted("ROLE_SUPER_ADMIN")) {
            return false;
        }

        return $this->security->isGranted("ROLE_SUPER_ADMIN") || in_array(self::SET_PERMISSIONS, $admin->getPermissions());
    }
}
