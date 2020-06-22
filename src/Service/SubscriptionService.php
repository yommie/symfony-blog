<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Subscription;
use App\Repository\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SubscriptionService {

    /**
     * @var EntityManagerInterface Entity Manager
     */
    private $entityManager;

    /**
     * @var AuthorizationCheckerInterface Entity Manager
     */
    private $authorizationChecker;

    /**
     * @var SubscriptionRepository Subscription Repository
     */
    private $subscriptionRepository;





    /**
     * Service Constructor
     * 
     * @param EntityManagerInterface Entity Manager
     * @param AuthorizationCheckerInterface Authorization Checker
     * @param SubscriptionRepository Subscription Repository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        AuthorizationCheckerInterface $authorizationChecker,
        SubscriptionRepository $subscriptionRepository
    ) {
       $this->entityManager = $entityManager;
       $this->authorizationChecker = $authorizationChecker;
       $this->subscriptionRepository = $subscriptionRepository;
    }





    /**
     * Subscribe to Author
     * 
     * @param User User
     * @param User Author
     * 
     * @throws \Exception
     * 
     * @return Subscription New subscription
     */
    public function subscribeToAuthor(
        User $user,
        User $author
    ): Subscription {
        if($this->subscriptionRepository->isSubscribed($user, $author)) {
            throw new \Exception("Subscription to this author already exists");
        }

        $subscription = new Subscription();
        $subscription->setUser($user);
        $subscription->setAuthor($author);
        $subscription->setCreatedDate(new \DateTime());

        $this->entityManager->persist($subscription);
        $this->entityManager->flush();

        $notificationService = new NotificationService($this->entityManager);

        try {
            $notificationService->createNotification($notificationService::USER_SUBSCRIBED_TO_AUTHOR, $user, $author);
        } catch(\Exception $e) {}

        return $subscription;
    }





    /**
     * Unsubscribe to Author
     * 
     * @param User User
     * @param User Author
     * 
     * @throws \Exception
     * 
     * @return bool
     */
    public function unsubscribeToAuthor(
        User $user,
        User $author
    ): bool {
        if(!$this->subscriptionRepository->isSubscribed($user, $author)) {
            throw new \Exception("Subscription to this author doesn not exist");
        }

        $subscription = $this->subscriptionRepository->findOneBy([
            "user" => $user,
            "author" => $author
        ]);

        $this->entityManager->remove($subscription);
        $this->entityManager->flush();

        return true;
    }
}