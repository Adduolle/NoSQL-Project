<?php
namespace App\Controller;

use App\Service\WaitManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CreateRoomController extends AbstractController
{

    private WaitManager $waitManager=new WaitManager();

    #[Route('/waitroom/normal_game', name: 'create_waitroom_normal')]
    public function createNormalRoom(): Response
    {
        $this->waitManager->createNormalRoom();
        return $this->render('waitroom.html.twig');
    }

    #[Route('/waitroom/path_game', name: 'create_waitroom_path')]
    public function createPathRoom(): Response
    {
        $this->waitManager->createRoomPath();
        return $this->render('waitroom.html.twig');
    }
}