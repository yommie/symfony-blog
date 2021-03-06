<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class NotificationService {

    public const USER_SUBSCRIBED_TO_AUTHOR = "JHHHZ1V4";
    public const AUTHOR_CREATED_NEW_ARTICLE = "zn3GFsbi";
    public const USER_COMMENTED_ON_POST = "TxYc4xU2";
    public const UPGRADED_TO_ADMIN = "FxAJ11gT";
    public const REMOVED_AS_ADMIN = "iObaBbhP";
    public const USER_BANNED = "wSgkTtfw";
    public const USER_UNBANNED = "KrPO4rmT";

    private const NOTIFICATION_TYPES = [
        self::USER_SUBSCRIBED_TO_AUTHOR,
        self::AUTHOR_CREATED_NEW_ARTICLE,
        self::USER_COMMENTED_ON_POST,
        self::UPGRADED_TO_ADMIN,
        self::REMOVED_AS_ADMIN,
        self::USER_BANNED,
        self::USER_UNBANNED
    ];

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
     * Create Notification
     * 
     * @param string Notification Type
     * @param User Actor
     * @param User Subject
     * @param string Object Id
     * @param string Description
     * 
     * @throws \Exception
     * 
     * @return Notification Created Notifiction
     */
    public function createNotification(
        string $type,
        User $actor,
        User $subject,
        string $objectId = null,
        string $description = null
    ): Notification {
        if(!in_array($type, self::NOTIFICATION_TYPES)) {
            throw new \Exception("Invalid notification type '$type' provided. Valid notification types are: " . implode(", ", self::NOTIFICATION_TYPES));
        }

        $notification = new Notification();
        $notification->setType($type);
        $notification->setActor($actor);
        $notification->setSubject($subject);
        $notification->setObjectId($objectId);
        $notification->setDescription($description);
        $notification->setIsSeen(false);
        $notification->setCreatedDate(new \DateTime());

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        return $notification;
    }





    /**
     * Format Notification
     * 
     * @param Notification Notification
     * @param Router Url Generator
     * 
     * @return array Formatted notification
     */
    public function formatNotification(
        Notification $notification,
        Router $router
    ): array {
        $formattedNotification = [
            "createdDate" => $notification->getCreatedDate()->format("jS F, Y h:i:s")
        ];

        switch($notification->getType()) {
            case self::USER_SUBSCRIBED_TO_AUTHOR:
                $formattedNotification["content"] = '<h2 class="post-title">' . $notification->getActor()->getUsername() . " subscribed to you" . '</h2>';
                $formattedNotification["link"] = $router->generate("viewAuthor", ["username" => $notification->getActor()->getUsername()]);
                break;

            case self::AUTHOR_CREATED_NEW_ARTICLE:
                $formattedNotification["content"] = '<h2 class="post-title">New article from ' . $notification->getActor()->getUsername() . '</h2>';
                $formattedNotification["content"] .= '<h3 class="post-subtitle">' . $notification->getDescription() . '</h3>';
                $formattedNotification["link"] = $router->generate("viewArticle", ["slug" => $notification->getObjectId()]);
                break;

            case self::USER_COMMENTED_ON_POST:
                $formattedNotification["content"] = '<h2 class="post-title">' . $notification->getActor()->getUsername() . ' commented on your article "' . $notification->getDescription() . '"</h2>';
                $formattedNotification["link"] = $router->generate("viewArticle", ["slug" => $notification->getObjectId()]);
                break;
            
            case self::UPGRADED_TO_ADMIN:
                $formattedNotification["content"] = '<h2 class="post-title">Account upgrade</h2>';
                $formattedNotification["content"] .= '<h3 class="post-subtitle">You have been upgraded to an admin</h3>';
                $formattedNotification["link"] = $router->generate("adminDashboard");
                break;
            
            case self::REMOVED_AS_ADMIN:
                $formattedNotification["content"] = '<h2 class="post-title">Account downgrade</h2>';
                $formattedNotification["content"] .= '<h3 class="post-subtitle">You have been removed as an admin</h3>';
                $formattedNotification["link"] = "#";
                break;
            
            case self::USER_BANNED:
                $formattedNotification["content"] = '<h2 class="post-title">Account ban</h2>';
                $formattedNotification["content"] .= '<h3 class="post-subtitle">You have been banned</h3>';
                $formattedNotification["link"] = "#";
                break;
            
            case self::USER_UNBANNED:
                $formattedNotification["content"] = '<h2 class="post-title">Account unban</h2>';
                $formattedNotification["content"] .= '<h3 class="post-subtitle">You have been unbanned</h3>';
                $formattedNotification["link"] = "#";
                break;
        }

        $notification->setIsSeen(true);
        $this->entityManager->flush();

        return $formattedNotification;
    }
}