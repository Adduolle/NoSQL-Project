<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestTwigController extends AbstractController
{
    #[Route('/testtwig', name: 'test_twig')]
    public function index(): Response
    {

        $mockUser = (object)[
            'nickname' => 'DemoUser',
            'id' => 1
        ];

        return $this->render('index.html.twig',
                        [
                            'user' => $mockUser
                        ]);
    }
}
