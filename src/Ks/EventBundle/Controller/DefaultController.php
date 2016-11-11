<?php

namespace Ks\EventBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Ks\EventBundle\Entity\Event;
//use Ks\EventBundle\Form\EventType;

class DefaultController extends Controller
{
    public function indexAction($numPage)
    {       
        // On récupère le repository
        $repository = $this->getDoctrine()
                           ->getEntityManager()
                           ->getRepository('KsEventBundle:Event');

        // On récupère le nombre total d'événements
        $nb_events = $repository->getTotal();

        // On définit le nombre d'événements par page
        $nb_events_page = 2;

        // On calcule le nombre total de pages
        $nb_pages = ceil($nb_events/$nb_events_page);
        $nb_pages = $nb_pages > 0 ? $nb_pages : 1;
        
        // On va récupérer les événements à partir du N-ième événement :
        $offset = ($numPage-1) * $nb_events_page;

        // Ici on a changé la condition pour déclencher une erreur 404
        // lorsque la page est inférieur à 1 ou supérieur au nombre max.
        if( $numPage < 1 OR $numPage > $nb_pages )
        {
            throw $this->createNotFoundException('Page inexistante (page = '. $numPage .')');
        }

        // On récupère les événements qu'il faut grâce à findBy() :
        $events = $repository->findBy(
            array(),                 // Pas de critère
            array('lastModificationDate' => 'desc'), // On tri par date décroissante
            $nb_events_page,       // On sélectionne $nb_events_page articles
            $offset                  // A partir du $offset ième
        );

        return $this->render('KsEventBundle:Default:index.html.twig', array(
            'events' => $events,
            'page'     => $numPage,    // On transmet à la vue la page courante,
            'nb_pages' => $nb_pages // Et le nombre total de pages.
        ));
    }
    
    public function voirEventAction($idEvent)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $event = $em->getRepository('KsEventBundle:Event')->find($idEvent);

        if (!$event) {
            throw $this->createNotFoundException('Impossible de trouver l\'événement ' . $idEvent . '.');
        }
        
        return $this->render('KsEventBundle:Default:voirEvent.html.twig', array(
            'event'      => $event
        ));
    }
    
    public function ajouterEventAction()
    {
        // On crée un objet Evenement.
        $event = new Event();

        // On crée le FormBuilder grâce à la méthode du contrôleur.
        $formBuilder = $this->createFormBuilder($event);

        // On ajoute les champs de l'entité que l'on veut à notre formulaire.
        $formBuilder
            ->add('name',    'text')
            //->add('author',   'text')
            ->add('content', 'textarea');
        

        // À partir du formBuilder, on génère le formulaire.
        $form = $formBuilder->getForm();
        
        // On récupère la requête.
        $request = $this->get('request');

        // On vérifie qu'elle est de type « POST ».
        if( $request->getMethod() == 'POST' )
        {
            // On fait le lien Requête <-> Formulaire.
            $form->bindRequest($request);

            // On vérifie que les valeurs rentrées sont correctes.
            // (Nous verrons la validation des objets en détail plus bas dans ce chapitre.)
            if( $form->isValid() )
            {
                
                //On récupère l'utilisateur connecté
                $user = $this->container->get('security.context')->getToken()->getUser();
                
                // Et pour vérifier que l'utilisateur est authentifié (et non un anonyme)
                if( ! is_object($user) )
                {
                    throw new AccessDeniedException('Vous n\'êtes pas authentifié.');
                }
                
                $event->setUser($user);

                // On l'enregistre notre objet $event dans la base de données.
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($event);
                $em->flush();

                // On redirige vers la page d'accueil, par exemple.
                return $this->redirect($this->generateUrl('ksevent_listerEvents'));
            }
        }

        // On passe la méthode createView() du formulaire à la vue
        // afin qu'elle puisse afficher le formulaire toute seule.
        return $this->render('KsEventBundle:Default:ajouterEvent.html.twig', array(
            'form' => $form->createView(),
        ));
    }
    
    public function editerEventAction($idEvent)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $event = $em->getRepository('KsEventBundle:Event')->find($idEvent);

        if (!$event) {
            throw $this->createNotFoundException('Impossible de trouver l\'événement ' . $idEvent . '.');
        }

        // On crée le FormBuilder grâce à la méthode du contrôleur.
        $formBuilder = $this->createFormBuilder($event);

        // On ajoute les champs de l'entité que l'on veut à notre formulaire.
        $formBuilder
            ->add('name',    'text')
            ->add('content', 'textarea');  

        // À partir du formBuilder, on génère le formulaire.
        $editForm = $formBuilder->getForm();

        $request = $this->getRequest();

        // On vérifie qu'elle est de type « POST ».
        if( $request->getMethod() == 'POST' )
        {
            $editForm->bindRequest($request);

            if ($editForm->isValid()) {
                
                $event->setLastModificationDate(new \Datetime());
                
                $em->persist($event);
                $em->flush();

                return $this->redirect($this->generateUrl('ksevent_listerEvents'));
            }
        }

        return $this->render('KsEventBundle:Default:editerEvent.html.twig', array(
            'event'      => $event,
            'form'   => $editForm->createView()
        ));
    }
    
    
    public function supprimerEventAction($idEvent)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $event = $em->getRepository('KsEventBundle:Event')->find($idEvent);

        if (!$event) {
            throw $this->createNotFoundException('Impossible de trouver l\'événement ' . $idEvent . '.');
        }

        $em->remove($event);
        $em->flush();
        
        return $this->redirect($this->generateUrl('ksevent_listerEvents'));
    }
    
}
