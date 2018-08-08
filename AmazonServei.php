<?php

use Aws\Common\Enum\Region;
use Aws\S3\Enum\CannedAcl;
use Aws\S3\S3Client;
use Aws\Ses\SesClient;

class AmazonServei {

    private static $m_pInstance;
    private $connexio;
    private $s3; //emmagatzament
    private $ses; //email

    private function __construct() {
        $this->connexio = ConnexioInterceptor::getInstance();


        require_once ('aws' . DIRECTORY_SEPARATOR . 'aws-autoloader.php');


        // Create a service building using shared credentials for each service
//        $aws = Aws::factory(array(
//                    'key' => AWS_PUBLIC_KEY,
//                    'secret' => AWS_PRIVATE_KEY,
//                    'region' => Region::US_WEST_2
//                ));

        $config = array(
            'key' => AWS_PUBLIC_KEY,
            'secret' => AWS_PRIVATE_KEY,
            'region' => Region::US_EAST_1
        );

        $this->s3 = S3Client::factory($config);
        $this->ses = SesClient::factory($config);
    }
/**
     * @return this
     */
    public static function getInstance() {
        if (!self::$m_pInstance) {
            self::$m_pInstance = new self();
        }

        return self::$m_pInstance;
    }

    //S3
    public function uploadObject($key, $body) {
        $obj = $this->s3->putObject(array(
            'Bucket' => BUCKET,
            'Key' => $key,
            'Body' => fopen($body, 'r'),
            'ACL' => CannedAcl::PRIVATE_ACCESS
        ));

        return $obj;
    }

//    public function uploadObject2($fp, $dir, $name, $mime, $w, $h, $path) {
//        $this->s3->putObject(array(
//            "Bucket" => BUCKET,
//            "Key" => $path,
//            "ContentType" => $mime,
//            "ACL" => CannedAcl::PRIVATE_ACCESS,
//            "Metadata" => array(
//                'width' => $w,
//                'height' => $h
//            ),
//            "Body" => $fp
//        ));
//    }
//
    public function uploadFolder($key) {
        return $this->s3->putObject(array(
                    'Bucket' => BUCKET,
                    'Key' => $key,
                    'Body' => $key,
                    'ContentType' => 'directory',
                    'ACL' => CannedAcl::PRIVATE_ACCESS
        ));
    }

    public function getObjects($path) {
        return $this->s3->listObjects(array(
                    'Bucket' => BUCKET,
                    'Prefix' => $path
        ));
    }

    public function getObjectURL($key, $expires = '+60 minutes') {
        return $this->s3->getObjectUrl(BUCKET, $key, $expires);
    }

    public function existeixObjecte($key) {
        return $this->s3->doesObjectExist(BUCKET, $key);
    }

    public function deleteObject($key) {
        echo 'key: '.$key;
        return $this->s3->deleteObject(array(
                    'Bucket' => BUCKET,
                    'Key' => $key
        ));
    }

    public function moveFile($source, $destination) {
        echo "Source: ".BUCKET.DIRECTORY_SEPARATOR.$source." - Destination: ".$destination;
        $this->s3->copyObject(array(
            'Bucket' => BUCKET,
            'CopySource' => BUCKET.DIRECTORY_SEPARATOR.$source,
            'Key' => $destination
        ));
        echo "aqui arriba";

        //Si hem pogut copiar l'arxiu, esborrem l'antic
        if ($this->existeixObjecte($destination)) {
            $this->s3->deleteObject(array(
                'Bucket' => BUCKET,
                'Key' => $source
            ));
        }
    }

    //SES
    public function sendEmail($adrecaDesti, $assumpte, $body, $adrecaEmissor = 'noreply@liorna.cat') {
        $adrecaDestiFiltrada = filter_var($adrecaDesti, FILTER_VALIDATE_EMAIL);

        if ($adrecaDestiFiltrada != FALSE) {
            try {

                $m = array(
                    'Source' => $adrecaEmissor,
                    'Destination' => array(
                        'ToAddresses' => array($adrecaDestiFiltrada)
//                'CcAddresses' => '',
//                'BccAddresses' => array('joan.galmes@gmail.com')
                    ),
                    'Message' => array(
                        'Subject' => array(
                            'Data' => strip_tags(html_entity_decode($assumpte)),
                            'Charset' => 'UTF-8'
                        ),
                        'Body' => array(
                            'Text' => array(
                                'Data' => strip_tags(html_entity_decode($body)),
                                'Charset' => 'UTF-8'
                            ),
                            'Html' => array(
                                'Data' => html_entity_decode($body),
                                'Charset' => 'UTF-8'
                            )
                        )
                    )
                );

                if (!DESENVOLUPADOR) {
                    $this->ses->sendEmail($m);
                }

                return true;
            } catch (Exception $e) {
                return false;
            }
        }
        return false;
    }


}

?>
