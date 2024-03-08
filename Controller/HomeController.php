<?php

namespace App\Controller;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
session_start();
class HomeController extends AbstractController
{
    public Request $request;
    #[Route('/home', name: 'get_home')]
    public function getHome(Request $request, EntityManagerInterface $entityManager): Response
    {
        //Взимаме си логнатия потребител
        $getuser = $entityManager->getRepository(Users::class)->findOneBy(array("username" => $_SESSION['user'], "password" => $_SESSION['password']));
        //При натискане на бутона да ни праща към играта , ако няма логнат потребител да ни праща към логина
            if ($request->request->get('singleGame') === '1' && $request->request->get('singleGame') == !null && $getuser) {
                $player = $request->request->get('player-x');
                return $this->redirectToRoute('single_game', [
                    'playerX' => $player
                ]);
            }
            if ($request->request->get('singleGame') === '1' && $request->request->get('singleGame') == !null && !$getuser) {
                return $this->redirectToRoute('get_login');
            }
            if ($request->request->get('multyGame') === '2' && $request->request->get('multyGame') == !null && $getuser) {
                $playerX = $request->request->get('player-x');
                $playerO = $request->request->get('player-o');
                return $this->redirectToRoute('multy_game', [
                    'playerX' => $playerX,
                    'playerO' => $playerO
                ]);
            }
            if ($request->request->get('multyGame') === '2' && $request->request->get('multyGame') == !null && !$getuser){
                return $this->redirectToRoute('get_login');
            }
            // Фунцкия за логаут
            if ($request->request->get('logout') === 'logout' && $request->request->get('logout') == !null) {
                $getuser=null;
                return $this->redirectToRoute('get_login');
            }
            return $this->render('home.html.twig' );
    }

}
