<?php

namespace App\Controller;

use AllowDynamicProperties;
use App\Entity\Users;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\NoReturn;
use App\Services\GameLogicSingle;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;


#[AllowDynamicProperties] class SingleGameController extends AbstractController
{
    //Създаваме си промернлива , от класа с логиката на играта и такива за сесий , рекуести и т.н.
    public Session $session;
    private GameLogicSingle $singleGameLogic;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        $this->singleGameLogic = new GameLogicSingle($requestStack);
    }

    #[Route('getSingle', name: 'single_game')]

    public function getSingle(Request $requestHandler,EntityManagerInterface $entityManager): Response
    {
        $getuser = $entityManager->getRepository(Users::class)->findOneBy(array("username" => $_SESSION['user'], "password" => $_SESSION['password']));
        if ($requestHandler->request->get('logout') === 'logout' && $requestHandler->request->get('logout') == !null) {
            $getuser=null;
            return $this->redirectToRoute('get_login');
        }
        //Взимаме си именто на играча чрез урл
        if ($player = $requestHandler->query->get('playerX')) {
            $_SESSION['player-x'] = $player ;
            }
        //Взимаме индекса на хода от url-a и си викаме функцията за хода с параметър този индекс
        if ($requestHandler->query->get('move')) {
             $indexData = $requestHandler->query->get('move');
             $this->singleGameLogic->makeMove($indexData);
            }
        //Викаме функций за изход и ресет спряма параметър от урл
        if ($requestHandler->query->get('exit')){
            return $this->exitGame();
            }
        if ($requestHandler->query->get('reset')) {
            return $this->resetGame();
        }
            //Взимаме си сесията сингълборд и сетваме победител и борд в самата сесия като ключове
            $session = $this->requestStack->getCurrentRequest()->getSession()->get('singleboard');
            $winner = $session['winner'] ?? '';
            $board = $session['board'] ?? '';
           /* $playerX = $session['playerX'] ?? '';*/
        //Проверяваме дали има логнат потребител и ако има рендърваме ме гейм страницата с ключовете от сесията
            if($getuser) {
                return $this->render('game.html.twig', [
                    'winner' => $winner,
                    'boards' => $board,
                    'playerX' => $_SESSION['player-x'],
                    'playerO' => "Бот"
                ]);
            }
            else return $this->redirectToRoute('get_login');
    }

    public function exitGame()
    {
        //Излизаме от играта
        $this->requestStack->getCurrentRequest()->getSession()->clear();
        return $this->redirectToRoute('get_home', []);
    }
    public function resetGame()
    {
        //Рефрешваме играта
        $this->requestStack->getCurrentRequest()->getSession()->clear();
        return $this->redirectToRoute('single_game', []);
    }
}
