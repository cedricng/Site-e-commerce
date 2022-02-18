<?php

namespace App\Controller;

use App\Classe\AWSS3;
use App\Classe\Cart;
use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{   private $s3;
    public function __construct(){
        $this->s3=new AWSS3();
    }
    /**
     * @Route("/mon-panier", name="cart")
     */
    public function index(Cart $cart): Response
    {
        $cartFull=$cart->getFull();
        foreach ($cartFull as $product){
            dd($product['product']);
            $product[0]=$this->s3->getS3Url($product[0]);
        }

        return $this->render('cart/index.html.twig',[
            'cart'=> $cartFull()
        ]);
    }

    /**
     * @Route("/cart/add/{id}", name="add_to_cart")
     */
    public function add(Cart $cart, $id): Response
    {
        $cart->add($id);
        return $this->redirectToRoute('cart');
    }

    /**
     * @Route("/cart/remove", name="remove_my_cart")
     */
    public function remove(Cart $cart): Response
    {
        $cart->remove($cart);
        return $this->redirectToRoute('products');
    }

    /**
     * @Route("/cart/delete/{id}", name="delete_from_cart")
     */
    public function delete(Cart $cart,$id): Response
    {
        $cart->delete($id);
        return $this->redirectToRoute('cart');
    }

    /**
     * @Route("/cart/decrease/{id}", name="decrease_from_cart")
     */
    public function decrease(Cart $cart,$id): Response
    {
        $cart->decrease($id);
        return $this->redirectToRoute('cart');
    }
}
