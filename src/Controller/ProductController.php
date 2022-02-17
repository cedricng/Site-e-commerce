<?php

namespace App\Controller;

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
    private $bucket;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager=$entityManager;
        $this->s3 = new S3Client([
            'version'  => '2006-03-01',
            'region'   => 'eu-west-3',
            'credentials' => array(
                'key' => 'AKIAYGMLEEYLJBG6QGGC',
                'secret'  => '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',
            )
        ]);
        $this->bucket = 'boutique-fr-ng';
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
             $product=$this->getS3Url($product, $this->bucket);
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
            $ind_product=$this->getS3Url($ind_product, $this->bucket);
        }
        if(!$product){
            return $this->redirectToRoute('products');
        }
        $product=$this->getS3Url($product, $this->bucket);
        return $this->render('product/show.html.twig',[
            'product'=>$product,
            'products'=> $products
        ]);
    }

    /**
     * @param $product
     * @param $bucket
     */
    public function getS3Url($product, $bucket):Object_
    {
        $fileKey = 'images/' . $product->getSlug() . '.jpg';


//Get a command to GetObject
        $cmd = $this->s3->getCommand('GetObject', [
            'Bucket' => $bucket,
            'Key' => $fileKey
        ]);

//The period of availability
        $awsRequest = $this->s3->createPresignedRequest($cmd, '+30 minutes');

//Get the pre-signed URL
        $signedUrl = (string)$awsRequest->getUri();

        $product->s3Url = $signedUrl;
        return $product;
    }
}
