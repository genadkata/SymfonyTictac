<?php

namespace App\Controller;

use App\Repository\UsersRepository;

use App\Entity\Users;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class VerificationController extends  AbstractController
{
    #[NoReturn] #[Route('/verify-password-change/{token}', name: 'verify_password_change')]
      public function verifyPasswordChange(string $token,  Request $request ,UsersRepository $userRepository, EntityManagerInterface $entityManager): RedirectResponse
      {
          //взимаме user спрямо сетнатия токен
          $user = $entityManager->getRepository(Users::class)->findOneBy(array("VerificationToken" => $token));
          // проверяваме дали има такъв user ,ако има сетваме новоата парола
          if (!$user/* || $user->getTokenExpiration() < new \Datetime()*/) {
              return $this->redirect('changePass.html.twig');
          }
          //сетваме си новата парола
          $newPassword = $request->query->get('newPassword');
          $hashedPassword = sha1($newPassword);
          $user->setPassword($hashedPassword);
          $entityManager->flush();

          return $this->redirectToRoute('get_login');
      }
}