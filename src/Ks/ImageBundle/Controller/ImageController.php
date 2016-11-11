<?php

namespace Ks\ImageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Ks\ImageBundle\Classes\UploadHandler;

class ImageController extends Controller
{
    /**
     * @Route("/upload/{uploadDirName}", requirements={"method" = "GET|POST|HEAD|PUT|DELETE"}, name = "ksImage_ajax_upload" )
     */
    public function uploadAction($uploadDirName) {
        $em = $this->getDoctrine()->getEntityManager();

        $request = $this->get('request');

        $uploadPhotosDirAbsolute = dirname($_SERVER['SCRIPT_FILENAME']). '/uploads/' . $uploadDirName .'/';
        if (! is_dir( $uploadPhotosDirAbsolute ) ) mkdir($uploadPhotosDirAbsolute);
        
        $uploadPhotosDirAbsolute_original = $uploadPhotosDirAbsolute . 'original/';
        if (! is_dir( $uploadPhotosDirAbsolute_original ) ) mkdir($uploadPhotosDirAbsolute_original);
        
        if( $uploadDirName == "clubs" || $uploadDirName == "users" || $uploadDirName == "shops" || $uploadDirName == "equipments" ) {
    
            $uploadPhotosDirAbsolute_1024x1024 = $uploadPhotosDirAbsolute . 'resize_1024x1024/';
            if (! is_dir( $uploadPhotosDirAbsolute_1024x1024 ) ) mkdir($uploadPhotosDirAbsolute_1024x1024);
            
            $uploadPhotosDirAbsolute_512x512 = $uploadPhotosDirAbsolute . 'resize_512x512/';
            if (! is_dir( $uploadPhotosDirAbsolute_512x512 ) ) mkdir($uploadPhotosDirAbsolute_512x512);
            
            $uploadPhotosDirAbsolute_128x128 = $uploadPhotosDirAbsolute . 'resize_128x128/';
            if (! is_dir( $uploadPhotosDirAbsolute_128x128 ) ) mkdir($uploadPhotosDirAbsolute_128x128);
            
            $uploadPhotosDirAbsolute_48x48 = $uploadPhotosDirAbsolute . 'resize_48x48/';
            if (! is_dir( $uploadPhotosDirAbsolute_48x48 ) ) mkdir($uploadPhotosDirAbsolute_48x48);


            $options = array(
                'upload_dir' => $uploadPhotosDirAbsolute_original,
                'upload_url' => $this->getFullUrl().'/uploads/' . $uploadDirName .'/original/',
                'image_versions' => array(
                    'original' => array(
                        'upload_dir' => $uploadPhotosDirAbsolute_1024x1024,
                        'upload_url' => $this->getFullUrl().'/uploads/' . $uploadDirName .'/resize_1024x1024/',
                        'max_height' => 1024,
                        'max_width'  => 1024
                    ),
                    'resize_512x512' => array(
                        'upload_dir' => $uploadPhotosDirAbsolute_512x512,
                        'upload_url' => $this->getFullUrl().'/uploads/' . $uploadDirName .'/resize_512x512/',
                        'max_height' => 512,
                        'max_width'  => 512
                    ),
                    'resize_128x128' => array(
                        'upload_dir' => $uploadPhotosDirAbsolute_128x128,
                        'upload_url' => $this->getFullUrl().'/uploads/' . $uploadDirName .'/resize_128x128/',
                        'max_height' => 128,
                        'max_width'  => 128
                    ),
                    'resize_48x48' => array(
                        'upload_dir' => $uploadPhotosDirAbsolute_48x48,
                        'upload_url' => $this->getFullUrl().'/uploads/' . $uploadDirName .'/resize_48x48/',
                        'max_height' => 48,
                        'max_width'  => 48
                    )
                )
            );
        } else {
            $uploadThumbnailPhotosDirAbsolute = $uploadPhotosDirAbsolute . 'thumbnail/';
            if (! is_dir( $uploadThumbnailPhotosDirAbsolute ) ) mkdir($uploadThumbnailPhotosDirAbsolute);
            $options = array(
                'upload_dir' => $uploadPhotosDirAbsolute_original,
                'upload_url' => $this->getFullUrl().'/uploads/' . $uploadDirName .'/original/',
                'image_versions' => array(
                    'original' => array(
                        'upload_dir' => $uploadPhotosDirAbsolute_original,
                        'upload_url' => $this->getFullUrl().'/uploads/' . $uploadDirName .'/original/',
                        'max_height' => 1200,
                        'max_width'  => 900
                    ),
                    'thumbnail' => array(
                        'upload_dir' => $uploadThumbnailPhotosDirAbsolute,
                        'upload_url' => $this->getFullUrl().'/uploads/' . $uploadDirName .'/thumbnail/',
                        'max_height' => 100,
                        'max_width'  => 150
                    )
                )
            );
        }
        
        $upload_handler = new \Ks\ImageBundle\Classes\UploadHandler($options, $this->generateUrl('ksImage_ajax_upload', array('uploadDirName' => $uploadDirName)));

        switch ($request->getMethod()) {
            case 'OPTIONS':
                break;
            case 'HEAD':
            case 'GET':
                $upload_handler->get();
                break;
            case 'POST':
                if ($request->get('_method') === 'DELETE') {
                    $upload_handler->delete();
                } else {
                    $upload_handler->post();
                }
                break;
            case 'DELETE':
                $upload_handler->delete();
                break;
            default:
                header('HTTP/1.1 405 Method Not Allowed');
        }

        $response = new Response();
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Content-Disposition', 'inline; filename="files.json"');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'OPTIONS, HEAD, GET, POST, PUT, DELETE');
        $response->headers->set('Access-Control-Allow-Headers', 'X-File-Name, X-File-Type, X-File-Size');
        return $response;
    }
    
    function getFullUrl() {
      	return
    		(isset($_SERVER['HTTPS']) ? 'https://' : 'http://').
    		(isset($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'].'@' : '').
    		(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'].
    		(isset($_SERVER['HTTPS']) && $_SERVER['SERVER_PORT'] === 443 ||
    		$_SERVER['SERVER_PORT'] === 80 ? '' : ':'.$_SERVER['SERVER_PORT']))).
    		substr($_SERVER['SCRIPT_NAME'],0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
    }
}