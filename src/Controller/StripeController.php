<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StripeController extends AbstractController
{
    /**
     * @Route("/commande/create-session/{reference}", name="stripe_create_session")
     */
    public function index(EntityManagerInterface $entityManager,Cart $cart,$reference)
    {
        $product_for_stripe = [];
        //$YOUR_DOMAIN = 'http://127.0.0.1:8000';
        $YOUR_DOMAIN = 'https://floating-savannah-98678.herokuapp.com/';

        $order=$entityManager->getRepository(Order::class)->findOneByReference($reference);

        if(!$order){
            new JsonResponse(['error'=>'order']);
        }
        foreach ($order->getOrderDetails()->getValues() as $product) {
            $product_object=$entityManager->getRepository(Order::class)->findOneByReference($reference);

            $product_for_stripe[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $product->getPrice(),
                    'product_data' => [
                        'name' => $product->getProduct(),
                        'images' => [$YOUR_DOMAIN],
                    ],
                ],
                'quantity' => $product->getQuantity(),
            ];
        }

        $product_for_stripe[] = [
            'price_data' => [
                'currency' => 'eur',
                'unit_amount' => $order->getCarrierPrice(),
                'product_data' => [
                    'name' => $order->getCarrierName(),
                    'images' => [$YOUR_DOMAIN],
                ],
            ],
            'quantity' => 1,
        ];


        Stripe::setApiKey('sk_test_51KQBTkHk4mbuoPKfGVjQhC8oN97pFq98DsgCoEz1yB2iBb9MxJsOBIXqlYXJIpgtIvDpN16PDtueXSdP6nWThO8W00UIdfpj46');

        $checkout_session = Session::create([
            'customer_email'=>$this->getUser()->getEmail(),
            'line_items' => [
                $product_for_stripe
            ],
            'payment_method_types' => [
                'card',
            ],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/commande/merci/{CHECKOUT_SESSION_ID}',
            'cancel_url' => $YOUR_DOMAIN . '/commande/erreur/{CHECKOUT_SESSION_ID}',
        ]);
        $order->setStripeSessionId($checkout_session->id);

        $entityManager->flush();
        return $this->redirect($checkout_session->url);
    }
}