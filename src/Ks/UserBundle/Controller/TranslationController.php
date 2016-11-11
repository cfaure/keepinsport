<?php
namespace Ks\UserBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;






class TranslationController extends Controller
{
    /**
     * @Route("/loadDatatables_translations", name = "ksTranslation_loadDatatables_translations", options={"expose"=true} )
     */
    public function loadDatatables_translationsAction() {
        $locale = $this->container->get('session')->getLocale();
        
        if( $locale == null ) $locale = "fr";
        
        $translations = array();
        switch( $locale ) {
            case "fr":
                $translations = array(
                    "sProcessing"=>     "Traitement en cours...",
                    "sSearch"=>         "Rechercher",
                    "sLengthMenu"=>     "Afficher _MENU_ &eacute;l&eacute;ments",
                    "sInfo"=>           "Affichage de l'&eacute;lement _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
                    "sInfoEmpty"=>      "Affichage de l'&eacute;lement 0 &agrave; 0 sur 0 &eacute;l&eacute;ments",
                    "sInfoFiltered"=>   "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
                    "sInfoPostFix"=>    "",
                    "sLoadingRecords"=> "Chargement en cours...",
                    "sZeroRecords"=>    "Aucun &eacute;l&eacute;ment &agrave; afficher",
                    "sEmptyTable"=>     "Aucune donnée disponible dans le tableau",
                    "oPaginate"=> array(
                        "sFirst"=>      "Premier",
                        "sPrevious"=>   "Pr&eacute;c&eacute;dent",
                        "sNext"=>       "Suivant",
                        "sLast"=>       "Dernier"
                    ),
                    "oAria"=> array(
                        "sSortAscending"=>  "=> activer pour trier la colonne par ordre croissant",
                        "sSortDescending"=> "=> activer pour trier la colonne par ordre décroissant"
                    )
                );
                break;
            
            case "en":
                $translations = array(
                    "sEmptyTable"=>     "No data available in table",
                    "sInfo"=>           "Showing _START_ to _END_ of _TOTAL_ entries",
                    "sInfoEmpty"=>      "Showing 0 to 0 of 0 entries",
                    "sInfoFiltered"=>   "(filtered from _MAX_ total entries)",
                    "sInfoPostFix"=>    "",
                    "sInfoThousands"=>  ",",
                    "sLengthMenu"=>     "Show _MENU_ entries",
                    "sLoadingRecords"=> "Loading...",
                    "sProcessing"=>     "Processing...",
                    "sSearch"=>         "Search",
                    "sZeroRecords"=>    "No matching records found",
                    "oPaginate"=> array(
                        "sFirst"=>    "First",
                        "sLast"=>     "Last",
                        "sNext"=>     "Next",
                        "sPrevious"=> "Previous"
                    ),
                    "oAria" => array(
                        "sSortAscending"=>  "=> activate to sort column ascending",
                        "sSortDescending"=> "=> activate to sort column descending"
                    )
                );
                break;
        }
        
        $response = new Response(json_encode($translations));
        $response->headers->set('Content-Type', 'application/json');

        
        return $response;
    }
    
    /**
     * @Route("/loadFullCalendar_translations", name = "ksTranslation_loadFullCalendar_translations", options={"expose"=true} )
     */
    public function loadFullCalendar_translationsAction() {
        $locale = $this->container->get('session')->getLocale();
        
        if( $locale == null ) $locale = "fr";
        
        $translations = "";
        switch( $locale ) {
            case "fr":
                $translations = "monthNames:['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'],
                    monthNamesShort:['janv.','févr.','mars','avr.','mai','juin','juil.','août','sept.','oct.','nov.','déc.'],
                    dayNames: ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'],
                    dayNamesShort: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
                    titleFormat: {
                        month: 'MMMM yyyy',
                        week: 'd[ MMMM][ yyyy]{ - d MMMM yyyy}',
                    day: 'dddd d MMMM yyyy'
                    },
                    columnFormat: {
                        month: 'ddd',
                    week: 'ddd d',
                    day: ''
                    },
                    axisFormat: 'H:mm', 
                    timeFormat: {
                        '': 'H:mm', 
                    agenda: 'H:mm{ - H:mm}'
                    },
                    firstDay:1,
                    buttonText: {
                        today: 'aujourd\'hui',
                        day: 'jour',
                        week:'semaine',
                        month:'mois'
                    },";
                break;
            
            case "en":
                $translations = "";/*"monthNames:['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                    monthNamesShort:['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    dayNames: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                    dayNamesShort: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                    titleFormat: {
                        month: 'MMMM yyyy',
                        week: 'd[ MMMM][ yyyy]{ - d MMMM yyyy}',
                    day: 'dddd d MMMM yyyy'
                    },
                    columnFormat: {
                        month: 'ddd',
                    week: 'ddd d',
                    day: ''
                    },
                    axisFormat: 'H:mm', 
                    timeFormat: {
                        '': 'H:mm', 
                    agenda: 'H:mm{ - H:mm}'
                    },
                    firstDay:1,
                    buttonText: {
                        today: 'today',
                        day: 'day',
                        week:'week',
                        month:'month'
                    },";*/
                break;
        }
        
        $response = new Response($translations);
        //$response->headers->set('Content-Type', 'application/json');

        
        return $response;
    }
}
?>
