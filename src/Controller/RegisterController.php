<?php

namespace App\Controller;

use App\Form\RegisterType;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegisterController extends AbstractController
{
    /**
     * @Route("/register", name="register")
     */
    public function register(
        Request $request,
        UserService $userService,
        AuthorizationCheckerInterface $authorizationChecker,
        UserPasswordEncoderInterface $passwordEncoder,
        TokenStorageInterface $tokenStorage
    ): Response {
        if($authorizationChecker->isGranted("IS_AUTHENTICATED_FULLY")) {
            return $this->redirectToRoute('home');
        }

        $registerForm = $this->createForm(RegisterType::class);

        $registerForm->handleRequest($request);

        if($registerForm->isSubmitted() && $registerForm->isValid()) {
            $data = $registerForm->getData();

            try {
                $user = $userService->createUser(
                    $passwordEncoder,
                    $data["username"],
                    $data["password"]
                );

                $userService->manualUserAuthentication($user, $request, $tokenStorage);

                $this->addFlash("home-success", "Your account was created successfully");

                return $this->redirectToRoute("home");
            } catch(\Exception $e) {
                $this->addFlash("register-error", $e->getMessage());
            }
        }

        return $this->render('register/index.html.twig', [
            "registerForm" => $registerForm->createView()
        ]);
    }
}
