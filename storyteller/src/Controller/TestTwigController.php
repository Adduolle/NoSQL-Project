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

        $story = [
        'title' => 'Histoire 1',
        'players' => [
            [
                'name' => 'Player 1',
                'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
            ],
            [
                'name' => 'Player 2',
                'text' => 'Ut enim ad minim veniam, quis nostrud exercitation.'
            ],
            [
                'name' => 'Player 3',
                'text' => 'Excepteur sint occaecat cupidatat non proident.'
            ],
            [
                'name' => 'Player 4',
                'text' => 'Excepteur sint occaecat cupidatat non proident,quis nostrud exercitation.'
            ]
            ]
            ];
    
        $mockUser = (object)[
        'nickname' => 'DemoUser',
        'id' => 1,
        'text' => 'Ceci est une histoire exemple créée par DemoUser.'
        ];


    
    return $this->render('resultat-histoire.html.twig', [
        'story' => $story,
        'user' => $mockUser
    ]);

}
}
