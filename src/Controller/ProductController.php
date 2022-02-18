<?php

namespace App\Controller;

use App\Classe\AWSS3;
use App\Classe\Search;
use App\Entity\Product;
use App\Form\SearchType;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Expr\Cast\Object_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Aws\S3\S3Client;

class ProductController extends AbstractController
{

    private $entityManager;
    private $s3;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager=$entityManager;
        $this->s3 = new AWSS3();
    }
    /**
     * @Route("/nos-produits", name="products")
     */
    public function index(Request $request): Response
    {

        $search=new Search();
        $form=$this->createForm(SearchType::class,$search);

        $form->handleRequest($request);
         if($form->isSubmitted() && $form->isValid()){
             $products=$this->entityManager->getRepository(Product::class)->findWithSearch($search);

         }else{
             $products=$this->entityManager->getRepository(Product::class)->findAll();

         }
         foreach ($products as $product){
             $product=$this->s3->getS3Url($product);
         }

        return $this->render('product/index.html.twig',[
            'products'=>$products,
            'form'=>$form->createView()
        ]);
    }
    /**
     * @Route("/produit/{slug}", name="product")
     */
    public function show($slug): Response
    {
        $product=$this->entityManager->getRepository(Product::class)->findOneBySlug($slug);
        $products= $this->entityManager->getRepository(Product::class)->findByIsBest(true);
        foreach ($products as $ind_product){
            $ind_product=$this->s3->getS3Url($ind_product);
        }
        if(!$product){
            return $this->redirectToRoute('products');
        }
        $product=$this->s3->getS3Url($product);
        return $this->render('product/show.html.twig',[
            'product'=>$product,
            'products'=> $products
        ]);
    }



}
