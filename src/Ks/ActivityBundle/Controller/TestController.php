<?php
namespace Ks\ActivityBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Leg\GoogleChartsBundle\Charts\Gallery\Bar\VerticalGroupedChart;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use vendor\symfony\src\Symfony\Bundle\TwigBundle\Extension\AssetsExtension;


class TestController extends Controller
{      
    /**
     * @Route("/reverseGeocoding", name = "test_reverseGeocoding" )
     */
    public function ReverseGeocodingAction()
    {
        $em          = $this->getDoctrine()->getEntityManager();
        $userRep     = $em->getRepository('KsUserBundle:User');
        $showcaseRep = $em->getRepository('KsTrophyBundle:Showcase');
        $showcaseExposesTrophiesRep = $em->getRepository('KsTrophyBundle:ShowcaseExposesTrophies');
        
        // set your API key here
        $api_key = "";
        // format this string with the appropriate latitude longitude
        //$url = 'http://maps.google.com/maps/geo?q=1.44420900,43.60465200&output=json&sensor=true_or_false&key=' . $api_key;
        $url = 'http://maps.google.com/maps/geo?q=43.600006,1.4423735&output=json&sensor=false';
        //$url = 'http://maps.googleapis.com/maps/api/geocode/json?address=1600+Amphitheatre+Parkway,+Mountain+View,+CA&sensor=true_or_false';
        // make the HTTP request
        $data = @file_get_contents($url);
        // parse the json response
        $jsondata = json_decode($data,true);
        // if we get a placemark array and the status was good, get the addres
        if(is_array($jsondata )&& $jsondata ['Status']['code']==200)
        {
              $full_address = $jsondata['Placemark'][0]['address'];
              $country_code = $jsondata['Placemark'][0]['AddressDetails']["Country"]["CountryNameCode"];
              $country_area = $jsondata['Placemark'][0]['AddressDetails']["Country"]['AdministrativeArea']["SubAdministrativeArea"]['SubAdministrativeAreaName'];
              $town = $jsondata['Placemark'][0]['AddressDetails']["Country"]['AdministrativeArea']["SubAdministrativeArea"]["Locality"]["LocalityName"];
              $longitude = $jsondata['Placemark'][0]["Point"]["coordinates"][0];
              $latitude = $jsondata['Placemark'][0]["Point"]["coordinates"][1];
        }

        var_dump($jsondata['Placemark'][0]["Point"]["coordinates"][0]);
        return $this->render('KsActivityBundle:Test:reverseGeocoding.html.twig', array(
            'form'                  => $trophyCategoryForm->createView(),
            'user'                  => $user
        ));
    }
    
    /**
     * @Route("/getAnnotationsForClass/{className}", name = "test_getAnnotationsForClass" )
     */
    public static function getAnnotationsForClassAction( $className ) { 
        
        $reader = new \Doctrine\Common\Annotations\AnnotationReader();  
        $reader->setDefaultAnnotationNamespace( __NAMESPACE__ );
        // Get the reflection class and return the annotations  
        $fullClassName = "\Ks\ActivityBundle\Entity\\" . $className;
        $class = new \ReflectionClass( $fullClassName );  
        
        $annotations = $reader->getClassAnnotations( $class );
        print_r( array_keys( $annotations[4]->value) );
        return $this->render('KsActivityBundle:Test:annotationsForClass.html.twig', array(
            'annotations'                  => $annotations
        )); 
    }  
  
}
