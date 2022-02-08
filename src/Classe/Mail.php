<?php

namespace App\Classe;

use Mailjet\Client;
use Mailjet\Resources;

class Mail{
    private $api_key='8fd54e906a193c06f1575eb853b59944';
    private $api_key_secret='926ef4ed40964018b9a02b5162544575';

    public function send($to_email,$to_name,$subject,$content)
    {
        $mj=new Client($this->api_key,$this->api_key_secret,true,['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "cedricngoupande@yahoo.com",
                        'Name' => "La Boutique Française"
                    ],
                    'To' => [
                        [
                            'Email' => $to_email,
                            'Name' => $to_name
                        ]
                    ],
                    'TemplateID' => 3595523,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'content' => "$content",

                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success();

    }


}