<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Classe\Mail;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderValidateController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager=$entityManager;
    }
    /**
     * @Route("/commande/merci/{stripeSessionId}", name="order_validate")
     */
    public function index(Cart $cart,$stripeSessionId): Response
    {

        $order=$this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);
        if(!$order || $order->getUser() != $this->getUser()){
            return $this->redirectToRoute('home');
        }
        if(!$order->getIsPaid()){
            //vide cart
            $cart->remove();

            //statut payé
            $order->setIsPaid(true);
            $this->entityManager->flush();

            //mail de confirmation
            $mail=new Mail();
            $content="Bonjour".$order->getUser()->getFirstName().
                "<br/> Merci pour votre commande<br/>
                    Lorem ipsum dolor sit amet, consectetur adipisicing elit.
                     Aliquam aliquid, cumque distinctio dolore et excepturi exercitationem in incidunt";
            $mail->send($order->getUser()->getEmail(),$order->getUser()->getFirstName(),'Votre commande sur la boutique Française est validée',$content);
        }

        return $this->render('order_validate/index.html.twig',[
            'order'=>$order
        ]);
    }
}
