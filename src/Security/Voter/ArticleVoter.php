<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Article;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ArticleVoter extends Voter
{
    private const CREATE = "CREATE_POST";
    private const EDIT = "EDIT_POST";
    private const DELETE = "DELETE_POST";
    private const COMMENT = "COMMENT_ON_POST";
    private const BAN_POST = "CAN_BAN_POST";

    private const SUPPORTED = [self::CREATE, self::EDIT, self::DELETE, self::COMMENT, self::BAN_POST];

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, self::SUPPORTED) && $subject instanceof Article;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        
        if(!$user instanceof UserInterface) {
            return false;
        }

        switch($attribute) {
            case self::CREATE:
                return $this->canCreate($user);
            case self::EDIT:
                return $this->canEdit($subject, $user);
            case self::DELETE:
                return $this->canDelete($subject, $user);
            case self::COMMENT:
                return $this->canComment($subject, $user);
            case self::BAN_POST:
                return $this->canBanArticle($user);
        }

        return false;
    }

    private function canCreate(User $user) {
        if($this->security->isGranted("ROLE_SUPER_ADMIN")) {
            return true;
        }

        return in_array(self::CREATE, $user->getPermissions());
    }

    private function canEdit(Article $article, User $user) {
        return $article->getAuthor() === $user && in_array(self::EDIT, $user->getPermissions());
    }

    private function canDelete(Article $article, User $user) {
        if($this->security->isGranted("ROLE_SUPER_ADMIN")) {
            return true;
        }
        
        return ($article->getAuthor() === $user || $this->security->isGranted("ROLE_ADMIN")) && in_array(self::DELETE, $user->getPermissions());
    }

    private function canComment(Article $article, User $user) {
        if(!$article->getIsCommentsAllowed()) {
            return false;
        }

        return $this->security->isGranted("ROLE_SUPER_ADMIN") || in_array(self::COMMENT, $user->getPermissions());
    }

    private function canBanArticle(User $user) {
        if(!$this->security->isGranted("ROLE_ADMIN")) {
            return false;
        }

        return $this->security->isGranted("ROLE_SUPER_ADMIN") || in_array(self::BAN_POST, $user->getPermissions());
    }
}
