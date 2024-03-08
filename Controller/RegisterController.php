<?php

namespace App\Controller;

use AllowDynamicProperties;
use App\Entity\Users;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;


#[AllowDynamicProperties] class RegisterController extends AbstractController
{
    public Request $request;
    #[Route('/register', name: 'get_register')]
    public function getHome(Request $request, EntityManagerInterface $entityManager , ValidatorInterface $validator ): Response
    {
        if($request->request->get('register') === 'register' && $request->request->get('register') == !null) {
            $user = new Users;
            $username = $request->request->get('user');
            $password = $request->request->get('password');
            $email = $request->request->get('email');

            $errors = $validator->validate($password,[
                new Assert\NotBlank (['message' => 'Please enter your password']),
                new Assert\Length(['min' => 6 ]) ,
                new Assert\Regex([
                    'pattern' => '/[A-Z]/',
                    'message' => 'Please add a uppercase letter'
                ]),
                new Assert\Regex([
                    'pattern' =>'/\W/',
                    'message' => 'Please add a special symbol'
                ])
            ]);
            // Тук работим с грешките от валидатора
            if(count($errors) > 0)
            {
                return $this->render('register.html.twig', [
                    'error' => $errors
                    ]);
            }
            $hashedPasword = sha1($password);
            $user->setPassword($hashedPasword);
            $user->setUsername($username);
            $user->setEmail($email);
            $entityManager->persist($user);
            //сетвамр си данните за потребителя
            $getuser = $entityManager->getRepository(Users::class)->findOneBy(array("username" => $username ) );
            $getpass = $entityManager->getRepository(Users::class)->findOneBy(array("password" => $hashedPasword) );
            $getemail = $entityManager->getRepository(Users::class)->findOneBy(array("email" => $email) );
            //Взимаме данни от таблицата и проверяваме дали има вече такива
            if ($user = $getuser || $user = $getpass || $user =$getemail)
            {
                $message = "Username,pasword or email already exist !";
                return $this->render('register.html.twig', [
                    'error' => $message
                ]);
            } else {
                $entityManager->flush();
            }
            return $this->redirectToRoute('get_login');
        }
        return $this->render('register.html.twig', [
            'error' => null
        ]);
    }
}