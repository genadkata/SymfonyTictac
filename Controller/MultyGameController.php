<?php

namespace App\Controller;

use AllowDynamicProperties;
use App\Entity\Users;
use App\Services\GameLogicMulty;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;


#[AllowDynamicProperties] class MultyGameController extends AbstractController
{
    public Session $session;
    public Request $requestHandler;
    private GameLogicMulty $multyGameLogic;


    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        $this->multyGameLogic = new GameLogicMulty($requestStack);
    }

    #[NoReturn] #[Route('getMulty', name: 'multy_game')]
    public function getMulty(Request $requestHandler, Session $session , EntityManagerInterface $entityManager): Response
    {
        $getuser = $entityManager->getRepository(Users::class)->findOneBy(array("username" => $_SESSION['user'], "password" => $_SESSION['password']));
        if ($requestHandler->request->get('logout') === 'logout' && $requestHandler->request->get('logout') == !null) {
            $getuser=null;
            return $this->redirectToRoute('get_login');
        }
        if ($requestHandler->query->get('exit')){
            return $this->exitGame();
        }
        if ($requestHandler->query->get('reset')){
            return $this->resetGame();
        }

        if ($player = $requestHandler->query->get('playerX')) {
            $_SESSION['player-x'] = $player ;
        }

        if ($playerO = $requestHandler->query->get('playerO')){
            $_SESSION['player-o'] = $playerO;
        }

        if ($requestHandler->query->get('move')) {
            $indexData = $requestHandler->query->get('move');
            $this->multyGameLogic->makeMove($indexData);
        }

        $session = $this->requestStack->getCurrentRequest()->getSession()->get('singleboard');
        $winner = $session['winner'] ?? '';
        $board = $session['board'] ?? '';
        $playerX = $session['playerX'] ?? '';
        if($getuser) {
            return $this->render('game.html.twig', [
                'winner' => $winner,
                'boards' => $board,
                'playerX' => $_SESSION['player-x'],
                'playerO' => $_SESSION['player-o']
            ]);
        }
        else return $this->redirectToRoute('get_login');
    }

    public function exitGame()
    {
        $this->requestStack->getCurrentRequest()->getSession()->clear();
        return $this->redirectToRoute('get_home', []);
    }
    public function resetGame()
    {
        $this->requestStack->getCurrentRequest()->getSession()->clear();
        return $this->redirectToRoute('multy_game', []);
    }
}

