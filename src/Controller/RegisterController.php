<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegisterController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager=$entityManager;
    }

    /**
     * @Route("/inscription", name="register")
     */
    public function index(Request $request,UserPasswordEncoderInterface $encoder): Response
    {
        $notification=null;
        $user = new User();
        $form=$this->createForm(RegisterType::class,$user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form-> isValid()){

            $user=$form->getData();

            $search_email=$this->entityManager->getRepository(User::class)->findOneByEmail($user->getEmail());

            if(!$search_email)
            {
                $password=$encoder->encodePassword($user,$user->getPassword());

                $user->setPassword($password);


                $this->entityManager->persist($user);
                $this->entityManager->flush();
                $notification="Votre inscription s'est bien déroulée, vous pouvez dès à présent accéder à votre compte";

                $mail=new Mail();
                $content="Bonjour".$user->getFirstName().
                    "<br/> Bienvenue sur la premiere boutique dediée au made in France<br/>
                    Lorem ipsum dolor sit amet, consectetur adipisicing elit.
                     Aliquam aliquid, cumque distinctio dolore et excepturi exercitationem in incidunt";
                $mail->send($user->getEmail(),$user->getFirstName(),'Bienvenue sur la boutique Française',$content);
            }
            else{
                $notification="L'email que vous avez renseigné existe déja";
            }

        }
        return $this->render('register/index.html.twig', [
            'form'=>$form->createView(),
            'notification'=>$notification
            ]);
    }
}
