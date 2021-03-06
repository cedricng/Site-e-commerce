<?php

namespace App\Classe;
use Aws\S3\S3Client;

class AWSS3
{
    private $s3;
    private $bucket;


    public function __construct()
    {

        $this->s3 = new S3Client([
            'version'  => '2006-03-01',
            'region'   => 'eu-west-3',
            'credentials' => array(
                'key' => '',
                'secret'  => '',
            )
        ]);
        $this->bucket = 'boutique-fr-ng';
    }

    public function getS3Url($product)
    {
        $fileKey = 'images/' . $product->getSlug() . '.jpg';


//Get a command to GetObject
        $cmd = $this->s3->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key' => $fileKey
        ]);

//The period of availability
        $awsRequest = $this->s3->createPresignedRequest($cmd, '+30 minutes');

//Get the pre-signed URL
        $signedUrl = (string)$awsRequest->getUri();

        $product->s3Url = $signedUrl;
        return $product;
    }
    public function getHeaderUrl($header,$key)
    {
        $fileKey = 'headers/' .$key. '.jpg';


//Get a command to GetObject
        $cmd = $this->s3->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key' => $fileKey
        ]);

//The period of availability
        $awsRequest = $this->s3->createPresignedRequest($cmd, '+30 minutes');

//Get the pre-signed URL
        $signedUrl = (string)$awsRequest->getUri();
        $header->s3Url = $signedUrl;
        return $header;
    }

    public function getFeaturettesUrl()
    {
        $fileKey1 = 'featurettes/first.jpg';
        $fileKey2 = 'featurettes/second.jpg';

//Get a command to GetObject
        $cmd1 = $this->s3->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key' => $fileKey1
        ]);
        $cmd2 = $this->s3->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key' => $fileKey2
        ]);

//The period of availability
        $awsRequest1 = $this->s3->createPresignedRequest($cmd1, '+30 minutes');
        $awsRequest2 = $this->s3->createPresignedRequest($cmd2, '+30 minutes');

//Get the pre-signed URL
        $signedUrl1 = (string)$awsRequest1->getUri();
        $signedUrl2 = (string)$awsRequest2->getUri();

        return [$signedUrl1,$signedUrl2];
    }
}
