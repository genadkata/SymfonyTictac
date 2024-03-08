<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class GameLogic
{
    //сетваме си сесията 'singleboard'
    const SINGLE_GAME = 'singleboard';

    public Request $request;
    // сетваме си нужните ключове за сесията
    protected array $board = [];
    protected string $playerX = 'X';
    protected string $winner = '';
    //контсруктор за създаване на сесия
    public function __construct(protected readonly RequestStack $requestStack)
    {
        //$this->requestStack->getCurrentRequest()->getSession()->clear();
        //Правим си проверки спрямо тази сесия и я сетваме
        if (
            $this->requestStack->getCurrentRequest()
            && $this->requestStack->getCurrentRequest()->getSession()
            && $this->requestStack->getCurrentRequest()->getSession()->has(self::SINGLE_GAME)
            && $this->requestStack->getCurrentRequest()->getSession()->get(self::SINGLE_GAME)
            ) {
                $gameData = $this->requestStack->getCurrentRequest()->getSession()->get(self::SINGLE_GAME);
                $this->board = $gameData['board']; // сетваме си борда
        } else {
            $this->winner = '';
            $this->playerX = 'X'; // инициализираме си победител и плеър
            $this->board = array_fill(0, 9, null); // инициализираме си борда като масив
            // сетваме си ключовете
            $this->requestStack->getCurrentRequest()->getSession()->set(self::SINGLE_GAME, [
                'board' => $this->board,
                'playerX' => $this->playerX,
                'winner' => ''
            ]);
        }
    }
    public function checkWinner()
    {
        $session = $this->requestStack->getCurrentRequest()->getSession();
        // Всички печеливши комбинаций
        $winningCombinations = array(
            [0, 1, 2], [3, 4, 5], [6, 7, 8], // По редове
            [0, 3, 6], [1, 4, 7], [2, 5, 8], // По колони
            [0, 4, 8], [2, 4, 6] // По диагонали
        );
        foreach ($winningCombinations as $combination) {
            $cell1 = $this->board[$combination[0]];
            $cell2 = $this->board[$combination[1]];
            $cell3 = $this->board[$combination[2]]; // взимаме комбиндцийте за победа
            //проверяваме ги
            if ($cell1 !== null && $cell1 === $cell2 && $cell1 === $cell3) {
                $winner = $cell1 == 'X' ? (isset($_SESSION['player-x']) ? $_SESSION['player-x'] :' Ти') : (isset($_SESSION['player-o']) ? $_SESSION['player-o'] : 'Бот');
                $this->winner = $winner;
                // сетваме си данните за победителя в променлива , която я придаване на ключа от нашата сесия winner
                $session->set(self::SINGLE_GAME , [
                    'board' => $this->requestStack->getCurrentRequest()->getSession()->get(self::SINGLE_GAME)['board'],
                    'playerX' => $this->playerX,
                    'winner' => $winner,
                ]);
                //Връщаме победителя
              return $winner;
            }
        }
    }
}