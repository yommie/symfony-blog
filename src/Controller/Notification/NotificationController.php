<?php

namespace App\Controller\Notification;

use App\Service\NotificationService;
use App\Repository\NotificationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NotificationController extends AbstractController
{
    /**
     * @Route("/app/notifications/{page}", name="notifications", requirements={"page"="\d+"})
     */
    public function notifications(
        int $page = 1,
        NotificationRepository $notificationRepository,
        NotificationService $notificationService,
        UrlGeneratorInterface $urlGeneratorInterface
    ): Response {
        $recordsPerPage = $this->getParameter("records_per_page");
        $notifications = $notificationRepository->fetchNotifications($recordsPerPage, $page, $this->getUser());
        $pages = ceil($notifications["totalMatched"] / $recordsPerPage);

        $formattedNotifications = [];
        foreach($notifications["paginator"] as $notification) {
            $formattedNotification = $notificationService->formatNotification(
                $notification,
                $urlGeneratorInterface
            );

            if(isset($formattedNotification["content"])) {
                $formattedNotifications[] = $formattedNotification;
            }
        }

        return $this->render('notification/index.html.twig', [
            "notifications" => $formattedNotifications,
            "notificationCount" => $notifications["totalMatched"],
            "page" => $page,
            "pages" => $pages
        ]);
    }
}
