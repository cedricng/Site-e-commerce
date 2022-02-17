<?php

namespace App\Controller;

use App\Classe\Search;
use App\Entity\Product;
use App\Form\SearchType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Aws\S3\S3Client;

class ProductController extends AbstractController
{

    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager=$entityManager;
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

        if(!$product){
            return $this->redirectToRoute('products');
        }
        $fileKey=$product->getSlug().'.jpg';
        $s3 = new S3Client([
            'version'  => '2006-03-01',
            'region'   => 'eu-west-3',
        ]);
        $bucket = getenv('S3_BUCKET')?: die('No "S3_BUCKET" config var in found in env!');
//Get a command to GetObject
        $cmd = $s3->getCommand('GetObject', [
            'Bucket' => $bucket,
            'Key'    => $fileKey
        ]);

//The period of availability
        $request = $s3->createPresignedRequest($cmd, '+30 minutes');

//Get the pre-signed URL
        $signedUrl = (string) $request->getUri();
        $product->s3Url=$signedUrl;
        return $this->render('product/show.html.twig',[
            'product'=>$product,
            'products'=> $products
        ]);
    }
}
