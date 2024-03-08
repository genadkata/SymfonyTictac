<?php

namespace App\Services;

use JetBrains\PhpStorm\NoReturn;

class GameLogicMulty extends GameLogic
{
    #[NoReturn] public function makeMove($index): void
    {
        $session = $this->requestStack->getCurrentRequest()->getSession();
        $this->board[$index] = 'X' ;
        $this->playerMove($index); // ход по индекс
        $this->checkWinner();

        $session->set(self::SINGLE_GAME, [
            'board' => $this->board,
            'playerX' => $this->playerX,
            'winner' => $this->winner
        ]);
    }
    //метод за сменяне на хода по дъската
    function playerMove($move): bool
    {
        if (isset($_SESSION['lastSymbol'])) {
            if ($_SESSION['lastSymbol'] == "X") {
                $this->board[$move] = 'O'; // Нашия ход
            } else {
                $this->board[$move] = 'X'; // Нашия ход
            }
            $_SESSION['lastSymbol'] = !$_SESSION['lastSymbol'];
            return true;
        } else {
            $_SESSION['lastSymbol'] = true;
            $this->playerMove($move);
        }
        return true;
    }
}