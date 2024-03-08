<?php

namespace App\Controller;

use App\Entity\Users;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends AbstractController
{
    public Request $request;
    public EntityManagerInterface $entityManager;
    #[Route('/', name: 'get_login')]
    public function getHome(Request $request, EntityManagerInterface $entityManager): Response
    {
        $username = $request->request->get('user');
        $password = $request->request->get('password');
        $hashPasword = sha1($password); // хешираме паролата
        // Пълним си данните в сесия , като сесийте могат да бъдат по друг начин , или юзъра да  бъде достъпен по друг начин
        $_SESSION['user'] = $username;
        $_SESSION['password'] = $hashPasword;

        $getuser = $entityManager->getRepository(Users::class)->findOneBy(array("username" => $username, "password" => $hashPasword ));
        //Правим си проверка спряма име и парола и ни препраща към хоъм пейджа
        if ($request->request->get('login') === 'login' && $request->request->get('login') == !null) {
            if ($getuser && $password == !null && $username == !null) {
                return $this->redirectToRoute('get_home');
            } else {
                $message = "Invalid username or password";
                return $this->render('login.html.twig', [
                    'error' => $message
                ]);
            }
        }

        if ($request->request->get('logout') === 'logout' && $request->request->get('logout') == !null) {
                $getuser=null;
            }
        //път към регистър
        if ($request->request->get('register') === 'register' && $request->request->get('register') == !null) {
            return $this->redirectToRoute('get_register', []);
        }
        //път към смяна на парола
        if ($request->request->get('change') === 'change' && $request->request->get('change') == !null) {
            return $this->redirectToRoute('get_change', []);
        }
        return $this->render('login.html.twig', [
            'error' => null
        ]);
    }
}

