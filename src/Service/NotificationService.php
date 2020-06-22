<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class NotificationService {

    public const USER_SUBSCRIBED_TO_AUTHOR = "JHHHZ1V4";
    public const AUTHOR_CREATED_NEW_ARTICLE = "zn3GFsbi";
    public const USER_COMMENTED_ON_POST = "TxYc4xU2";

    private const NOTIFICATION_TYPES = [self::USER_SUBSCRIBED_TO_AUTHOR, self::AUTHOR_CREATED_NEW_ARTICLE, self::USER_COMMENTED_ON_POST];

    /**
     * Entity Manager
     * 
     * @var EntityManagerInterface Entity Manager
     */
    private $entityManager;

    /**
     * Authorization Checker
     * 
     * @var AuthorizationCheckerInterface Entity Manager
     */
    private $authorizationChecker;





    /**
     * Service Constructor
     * 
     * @param EntityManagerInterface Entity Manager
     * @param AuthorizationCheckerInterface Authorization Checker
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
       $this->entityManager = $entityManager;
       $this->authorizationChecker = $authorizationChecker;
    }





    /**
     * Create Notification
     * 
     * @param string Notification Type
     * @param User Actor
     * @param User Subject
     * @param string Object Id
     * 
     * @throws \Exception
     * 
     * @return Notification Created Notifiction
     */
    public function createNotification(
        string $type,
        User $actor,
        User $subject,
        string $objectId = null
    ): Notification {
        if(!in_array($type, self::NOTIFICATION_TYPES)) {
            throw new \Exception("Invalid notification type '$type' provided. Valid notification types are: " . implode(", ", self::NOTIFICATION_TYPES));
        }

        $notification = new Notification();
        $notification->setType($type);
        $notification->setActor($actor);
        $notification->setSubject($subject);
        $notification->setObjectId($objectId);
        $notification->setIsSeen(false);
        $notification->setCreatedDate(new \DateTime());

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        return $notification;
    }
}