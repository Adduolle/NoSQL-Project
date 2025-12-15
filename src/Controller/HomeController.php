<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\PlayerManager;

class HomeController extends AbstractController
{
    #[Route('/', name: 'homepage', methods: ['GET', 'POST'])]
    public function index(Request $request, PlayerManager $playerManager): Response
    {
        $user = $this->getUser();
        $userId = null;
        if ($user) {
            if (is_object($user) && method_exists($user, 'getUserIdentifier')) {
                $userId = $user->getUserIdentifier();
            } else {
                $userId = (string) $user;
            }
        }

        [$token, $cacheKey, $item, $data] = $playerManager->loadOrCreate($request, $userId);

        $currentUsername = $data['username'] ?? null;
        $error = null;
        $success = null;

        $response = $this->render('index.html.twig', [
            'user_id' => $token,
            'username' => $currentUsername,
            'error' => $error,
            'success' => $success,
        ]);

        // Set cookie
        if ($token) {
            $cookie = Cookie::create(
                $playerManager->getCookieName(),
                $token,
                time() + $playerManager->getTtl(),
                '/',
                null,
                false,
                true,
                false,
                Cookie::SAMESITE_LAX
            );
            $response->headers->setCookie($cookie);
        }

        return $response;
    }
}
