<?php

namespace App\Services;

use JetBrains\PhpStorm\NoReturn;

class GameLogicSingle extends GameLogic
{
    //Метод за хода
    #[NoReturn] public function makeMove($index): void
    {
        //Взимаме си сесията
        $session = $this->requestStack->getCurrentRequest()->getSession();
        //сетваме си движението на борда спрямо индекс от урл
        $this->board[$index] = 'X';
        //хода на бота
        $emptyCells = array_filter($this->board, function ($cell)  // Взимаме си празните клетки
        {
            return $cell === null;
        });

        if (!empty($emptyCells))
        { // Проверяваме дали са празни
            $randomCell = array_rand($emptyCells); // Избира рандом клетка
            $this->board[$randomCell] = "O"; // Сетваме хода на бота//
        }
        //метода за победител
        $this->checkWinner();
        //сетваме си новите данни в сесията
        $session->set(self::SINGLE_GAME, [
            'board' => $this->board,
            'playerX' => $this->playerX,
            'winner' => $this->winner
        ]);
    }
}