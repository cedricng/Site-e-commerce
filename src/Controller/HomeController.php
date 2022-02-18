<?php

namespace App\Controller;

use App\Classe\AWSS3;
use App\Classe\Mail;
use App\Entity\Header;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private $entityManager;
    private $s3;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager=$entityManager;
        $this->s3 = new AWSS3();
    }
    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        $products= $this->entityManager->getRepository(Product::class)->findByIsBest(true);
        $headers =$this->entityManager->getRepository(Header::class)->findAll();
        foreach($headers as $key=>$header){
            dd(key+1);
            $product=$this->s3->getHeaderUrl($header,$key+1);
        }
        foreach ($products as $product){
            $product=$this->s3->getS3Url($product);
        }
        return $this->render('home/index.html.twig',[
            'products'=>$products,
            'headers' => $headers
        ]);
    }
}
