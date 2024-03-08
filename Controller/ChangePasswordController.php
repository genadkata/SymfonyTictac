<?php

namespace App\Controller;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ChangePasswordController extends AbstractController
{
    /*private const ExpirationTime = '+1hour';*/
    /**
     * @throws TransportExceptionInterface
     * @throws TransportExceptionInterface
     */
    #[Route('/change', name: 'get_change')]
    public function getChange(
        Request $request,
        EntityManagerInterface $entityManager ,
        ValidatorInterface $validator,
        MailerInterFace $mailer
    ): Response
    {
        //Custom errors
        $errorsMessages = [1 => "Please enter a valid email" ,
            2 => 'Password are not the same' ,
            3 => 'Password must contain uppercase letter , special symbol and at least 6 symbols' ,
            4 => 'Email was send successfully. Please verify your password'];

        if ($request->request->get('change') === 'change' && $request->request->get('change') == !null){
        // Инициализираме си пост данните от формата спрямо ПОСТ заявка
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirmPassword');
        // Правим проверки за паролата дължина, символ , главна буква
            $errors = $validator->validate($password,[
                new Assert\NotBlank ([]),
                new Assert\Length(['min' => 6 ]) ,
                new Assert\Regex( [
                    'pattern' => '/[A-Z]/',
                ]),
                new Assert\Regex([
                    'pattern' =>'/\W/',
                ])
            ]);
            //Ако има грешка при паролата да се визуализира съобшение
            if(count($errors) > 0)
            {
                return $this->render('register.html.twig', [
                    'error' => $errorsMessages[3]
                ]);
            }
            //Проверка за съществуващ имейл и съвпадение на парола
            $getuser = $entityManager->getRepository(Users::class)->findOneBy(array("email" => $email));
           if(!$getuser) {
                return $this->render('changePass.html.twig', [
                    'error' => $errorsMessages[1]
                ]);
            }
            if($password !== $confirmPassword){
                return $this->render('changePass.html.twig', [
                    'error' => $errorsMessages[2]
                    ]);
            }
            // създаваме си токен на user за верификация с имейл
            $token = md5(uniqid("",true));
            /*$expirationDate = new \Datetime(self::ExpirationTime);*/
            /*$getuser->setTokenExpirations($expirationDate);*/
            $getuser->setVerificationToken($token);
            $entityManager->persist($getuser);
            $entityManager->flush();
            //Създаваме линк за верификация с път към контролер който обработва данните за токена
            $verificationLink = $this->generateUrl('verify_password_change', ['token' => $token , 'newPassword' => $password], UrlGeneratorInterface::ABSOLUTE_URL);
            //Изпращаме имейла с линка
           if($email = (new Email())
                ->from('evgeni@abv.bg')
                ->to($email)
                ->subject('Verification Email')
                ->html("<p>Please click <a href='$verificationLink'>here</a> to verify your password change.</p>")
        )
               $mailer->send($email);
            {
                return $this->render('changePass.html.twig', [
                    'error' => $errorsMessages[4]
                    ]);
            }
        }
        return $this->render('changePass.html.twig', [
            'error' => null,
            ]);
    }
}