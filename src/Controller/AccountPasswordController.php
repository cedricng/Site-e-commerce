<?php

namespace App\Controller;

use App\Form\ChangePasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AccountPasswordController extends AbstractController
{
    /**
     * @Route("/compte/modifier-mon-mot-de-passe", name="account_password")
     */
    public function index(Request $request,UserPasswordEncoderInterface $encoder): Response
    {
        $notification=null;
        $user=$this->getUser();
        $form=$this->createForm(ChangePasswordType::class,$user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $old_pwd=$form->get('old_password')->getData();
            if($encoder->isPasswordValid($user,$old_pwd)){
                $new_pwd=$form->get('new_password')->getData();

                $password=$encoder->encodePassword($user,$new_pwd);

                $user->setPassword($password);

                $doctrine=$this->getDoctrine()->getManager();

                $doctrine->flush();
                $notification='Votre mot de passe a bien été mis à jour';
            }else{
                $notification="Votre mot de passe actuel n'est pas le bon";
            }
        }
        return $this->render('account/password.html.twig',[
            'form'=> $form->createView(),
            'notification'=>$notification
        ]);
    }
}
