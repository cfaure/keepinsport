<?php

namespace Ks\ActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Article controller.
 *
 * 
 */
class ArticleController extends Controller
{
    /**
     * Lists all Article entities.
     *
     * @Route("/all", name="ksArticle_list")
     * @Template()
     */
    public function indexAction()
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();
        $articleRep         = $em->getRepository('KsActivityBundle:Article');
        $articleTagRep      = $em->getRepository('KsActivityBundle:ArticleTag');
        $modificationsRep   = $em->getRepository('KsActivityBundle:UserModifiesArticle');
        $articleTags        = $articleTagRep->findAll();
        
        //Récupération de la session
        $session = $this->get('session');
        $session->set('pageType', 'wikisport');
        $session->set('page', 'listArticles');
        
        $articleCategories  = $articleTagRep->findBy(array("isCategory" => true));
        $articleCategoriesId = array();
        $lastArticlesByCategories = array();
        $articlesByCategories = array();
        foreach( $articleCategories as $articleCategoriy ) {
            $categoryId = $articleCategoriy->getId();
            $articleCategoriesId[] = $categoryId;
            $lastArticlesByCategories[$categoryId] = array();
            $lastSubscriptionsByCategories[$categoryId] = array();
            $subscriptionsByCategories[$categoryId] = array();
        }
        
        $articles = $articleRep->findAll();
        if( is_object( $user )) {
            $subscriptions = $articleRep->findMyScubscriptions($user->getId());
        } else {
            $subscriptions = array();
        }
        
        
        //On trie les articles par date de dernière modification
        usort( $articles, array( $this, "orderByArticlesByDateDesc" ) );
        usort( $subscriptions, array( $this, "orderByArticlesByDateDesc" ) );

        //construction de tableaux d'articles indexés par catégories
        foreach( $articles as $article ) {  
            $lastArticleModifications = $modificationsRep->getLastModification( $article );
            if ( ! empty($lastArticleModifications)) {
                
                $articleContent = json_decode($lastArticleModifications->getContent(), true);
                $intersect = array_intersect( $articleContent["tags"], $articleCategoriesId );
                
                if( count( $intersect ) > 0 ) {
                    $categoryId = array_shift( $intersect );

                    $articlesByCategories[$categoryId][$article->getId()] = $article;
                    if( count ( $lastArticlesByCategories[$categoryId] ) < 10 ) {
                        $lastArticlesByCategories[$categoryId][] = $article;
                    }
                }               
            } 
        }
        
        //construction de tableaux d'articles auxquels je suis abonné, indexés par catégories
        foreach( $subscriptions as $article ) {  
            $lastArticleModifications = $modificationsRep->getLastModification( $article );
            if ( ! empty($lastArticleModifications)) {
                
                $articleContent = json_decode($lastArticleModifications->getContent(), true);
                $intersect = array_intersect( $articleContent["tags"], $articleCategoriesId );
                
                if( count( $intersect ) > 0 ) {
                    $categoryId = array_shift( $intersect );

                    $subscriptionsByCategories[$categoryId][$article->getId()] = $article;
                    if( count ( $lastArticlesByCategories[$categoryId] ) < 10 ) {
                        $lastSubscriptionsByCategories[$categoryId][] = $article;
                    }
                }               
            } 
        }
        
        
        //Récupération du formulaire d'ajout d'article  
        if( is_object( $user )) {
            $article      = new \Ks\ActivityBundle\Entity\Article($user);
        } else {
            $article      = new \Ks\ActivityBundle\Entity\Article();
        }
        $articleForm  = $this->createForm(new \Ks\ActivityBundle\Form\ArticleType(), $article);
        
        return $this->render('KsActivityBundle:Article:article_index.html.twig', array(
            'articles'                  => $articles,
            'articleTags'               => $articleTags,
            'lastArticlesByCategories'  => $lastArticlesByCategories,
            'articlesByCategories'      => $articlesByCategories,
            'articleCategories'         => $articleCategories,
            'articleCategoriesId'       => $articleCategoriesId,
            'lastSubscriptionsByCategories'  => $lastSubscriptionsByCategories,
            'subscriptionsByCategories'      => $subscriptionsByCategories,
            'articleForm'   => $articleForm->createView(),
        ));
    }
    
    /**
     * Lists all Article entities.
     *
     */
    public function navAction()
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();
        $articleTagRep      = $em->getRepository('KsActivityBundle:ArticleTag');
        
        $articleCategories  = $articleTagRep->findBy(array("isCategory" => true));
        
        return $this->render('KsActivityBundle:Article:_nav.html.twig', array(
            'articleCategories'               => $articleCategories,

        ));
    }
    
    /**
     * Lists all Article entities.
     *
     * @Route("/category/{tagId}/list", name="ksArticle_category")
     * @Template()
     */
    public function categoryAction($tagId)
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();
        $activityRep         = $em->getRepository('KsActivityBundle:Activity');
        $articleTagRep      = $em->getRepository('KsActivityBundle:ArticleTag');
        
        //Récupération de la session
        $session = $this->get('session');
        $session->set('pageType', 'wikisport');
        
        $category = $articleTagRep->find($tagId);
        
        $articles = $activityRep->findActivities(array(
            'categoryTagId' => $tagId
        ));
        
        //var_dump($articles);
        
        return array(
            "category" => $category,
            "articles" => $articles
        );
    }
    
    private function orderByArticlesByDateDesc($article1, $article2)
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $modificationsRep  = $em->getRepository('KsActivityBundle:UserModifiesArticle');
        
        $lastArticle1Modifications = $modificationsRep->getLastModification($article1);
        $lastArticle2Modifications = $modificationsRep->getLastModification($article2);
        
        if (!$lastArticle1Modifications || !$lastArticle2Modifications) {
            return 0;
        }
        
        if ( $lastArticle1Modifications->getModifiedAt() > $lastArticle2Modifications->getModifiedAt() ) {
            return -1;
        }
        elseif ( $lastArticle1Modifications->getModifiedAt() < $lastArticle2Modifications->getModifiedAt() ) {
            return 1;
        } else {
            return 0;
        }
    }
    
    /**
     * Récupérer les articles auquels je suis abonné
     *
     * @Route("/my_subscriptions", name="ksArticle_my_subscriptions")
     * @Template()
     */
    public function mySubscriptionsAction()
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();
        $articleRep         = $em->getRepository('KsActivityBundle:Article');
        $articleTagRep      = $em->getRepository('KsActivityBundle:ArticleTag');
        $modificationsRep   = $em->getRepository('KsActivityBundle:UserModifiesArticle');
        $articleTags = $articleTagRep->findAll();
        
        $articleCategories  = $articleTagRep->findBy(array("isCategory" => true));
        $articleCategoriesId = array();
        $lastArticlesByCategories = array();
        $articlesByCategories = array();
        foreach( $articleCategories as $articleCategoriy ) {
            $categoryId = $articleCategoriy->getId();
            $articleCategoriesId[] = $categoryId;
            $lastArticlesByCategories[$categoryId] = array();
            $articlesByCategories[$categoryId] = array();
        }
        
        $articles = $articleRep->findMyScubscriptions($user->getId());
        
        //On trie les articles par date de dernière modification
        usort( $articles, array( $this, "orderByArticlesByDateDesc" ) );

        //construction de tableaux d'articles indexés par catégories
        foreach( $articles as $article ) {  
            $lastArticleModifications = $modificationsRep->getLastModification( $article );
            if ( ! empty($lastArticleModifications)) {
                
                $articleContent = json_decode($lastArticleModifications->getContent(), true);
                $intersect = array_intersect( $articleContent["tags"], $articleCategoriesId );
                
                if( count( $intersect ) > 0 ) {
                    $categoryId = array_shift( $intersect );

                    $articlesByCategories[$categoryId][$article->getId()] = $article;
                    if( count ( $lastArticlesByCategories[$categoryId] ) < 10 ) {
                        $lastArticlesByCategories[$categoryId][] = $article;
                    }
                }               
            } 
        }
        
        
        //Récupération du formulaire d'ajout d'article       
        $article      = new \Ks\ActivityBundle\Entity\Article($user);
        $articleForm  = $this->createForm(new \Ks\ActivityBundle\Form\ArticleType(), $article);
        
        return $this->render('KsActivityBundle:Article:article_index.html.twig', array(
            'articles'                  => $articles,
            'articleTags'               => $articleTags,
            'lastArticlesByCategories'  => $lastArticlesByCategories,
            'articlesByCategories'      => $articlesByCategories,
            'articleCategories'         => $articleCategories,
            'articleCategoriesId'       => $articleCategoriesId,
            'articleForm'   => $articleForm->createView(),
        ));
    }
    
    /**
     * Récupérer mes articles
     *
     * @Route("/search_tags", name="ksArticle_searchArticlesByTags")
     * @Template()
     */
    public function searchByTagsAction()
    {  
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();
        $articleRep         = $em->getRepository('KsActivityBundle:Article');
        $request            = $this->getRequest();
        
        $parameters = $request->request->all();
        $articleTagsId = isset( $parameters["articleTagsId"] ) ? $parameters["articleTagsId"] : array() ;
        
        $articles = $articleRep->findByTagsId($articleTagsId);
 
        return $this->render('KsActivityBundle:Article:article_list.html.twig', array(
            'articles'       => $articles
        ));
    }
    
    /**
     * Récupérer mes articles
     *
     * @Route("/search_tag/{tagId}", name="ksArticle_searchArticleByTag")
     * @Template()
     */
    public function searchByTagAction($tagId)
    { 
        $em                 = $this->getDoctrine()->getEntityManager();
        $articleRep         = $em->getRepository('KsActivityBundle:Article');
        
        $articles = $articleRep->findByTagsId(array($tagId));
 
        return $this->render('KsActivityBundle:Article:article_list.html.twig', array(
            'articles'       => $articles
        ));
    }

    /**
     * Finds and displays a Article entity.
     *
     * @Route("/{articleId}/show", name="ksArticle_show", options={"expose"=true})
     */
    public function showAction($articleId)
    {        
        $em                 = $this->getDoctrine()->getEntityManager();
        $modificationsRep   = $em->getRepository('KsActivityBundle:UserModifiesArticle');
        $articleTagRep      = $em->getRepository('KsActivityBundle:ArticleTag');
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        $articleRep         = $em->getRepository('KsActivityBundle:Article');
        
        $tags               = $articleTagRep->findAll();
        $article = $articleRep->find($articleId);

        if (!$article) {
            throw $this->createNotFoundException('Unable to find Article entity.');
        }
        
        $article->numVotes  = $activityRep->getNumVotesOnActivity($article);
        $lastArticleModifications = $modificationsRep->getLastModification($article);
        
        if ( ! empty($lastArticleModifications)) {
            $articleContent = json_decode($lastArticleModifications->getContent(), true);
            
            if( isset( $articleContent["title"] )) {
                $articleContent["title"] = base64_decode($articleContent["title"]);
            } else {
                $articleContent["title"] = $article->getLabel();
            }
            
            //Si le titre n'est pas encore versionné
            if ( empty( $articleContent["title"] ) ) $articleContent["title"] = $article->getLabel();
            
            //On decode les champs de textes et les tableaux
            foreach ( $articleContent["elements"] as $elementKey => $element ) {
                $articleContent["elements"][$elementKey]["title"] = base64_decode($element["title"]);
                $tablesOrParagraphs = array();
                
                foreach( $element["content"] as $tableOrParagraph ) {
                    $contentSubtitle = isset( $tableOrParagraph["subtitle"] ) ? base64_decode( $tableOrParagraph["subtitle"] ) : "";                   
                    $type = $tableOrParagraph["type"];
                    if( $type == "table" ) {
                        $content = array(
                            "head" => array(),
                            "body" => array()
                        );

                        //On encode l'entête
                        if ( isset( $tableOrParagraph["content"]["head"] ) ) {
                            foreach( $tableOrParagraph["content"]["head"] as $iRow => $row ) {
                                foreach( $row as $column ) {
                                    $content["head"][$iRow][] = base64_decode( $column );
                                }
                            }
                        }

                        //On encode le reste du tableau
                        if ( isset( $tableOrParagraph["content"]["body"] ) ) {
                            foreach( $tableOrParagraph["content"]["body"] as $iRow => $row ) {
                                foreach( $row as $column ) {
                                    $content["body"][$iRow][] = base64_decode( $column );
                                }
                            }
                        }
                    } else {
                        $content = isset( $tableOrParagraph["content"] ) ? base64_decode($tableOrParagraph["content"]) : "";
                    }

                    $tablesOrParagraphs[] = array(
                        "type"      => $type,
                        "subtitle"  => $contentSubtitle,
                        "content"   => $content
                    );
                }
                $articleContent["elements"][$elementKey]["content"] = $tablesOrParagraphs;                     
            }
            
            $articleContent["description"] = base64_decode($articleContent["description"]);
            
            //On décode le plan d'entrainement
             //On encode en utf8 le plan d'entrainement s'il y en a un
            if( ! empty( $articleContent["trainingPlan"] ) ) {
                foreach ( $articleContent["trainingPlan"] as $numWeek => $week ) {
                    foreach ( $week as $numSession => $session ) {
                        $articleContent["trainingPlan"][$numWeek][$numSession]["description"] = base64_decode($session["description"]);
                    }  
                }
            }
        } else {
            $tag = $articleTagRep->findOneByLabel("Article");
            
            if ( $tag) {
                $articleContent = array(
                    "title"         => "",
                    "description"   => "",
                    "elements"      => array(),
                    "photos"        => array(),
                    "tags"          => array($tag->getId())
                );
            } else {
                $articleContent = array();
            } 
        }

        //On récupère l'historique décroissant des modifications
        $historicModifications = $modificationsRep->findBy(
                array("article" => $article->getId()),
                array("modifiedAt" =>"desc")
        );
        
        $article->numWarnings          = (int)$activityRep->getNumWarningsOnActivity($article);
        
        return $this->render('KsActivityBundle:Article:article_show.html.twig', array(
            'article'               => $article,
            'articleContent'        => $articleContent,
            'historicModifications' => $historicModifications,
            'tags'                  => $tags,
            'comments'              => $activityRep->getCommentsOnActivity($article)
        ));
    }

    /**
     * Displays a form to create a new Article entity.
     *
     * @Route("/new", name="ksArticle_new")
     */
    public function getArticleFormAction()
    {   
        $em                 = $this->getDoctrine()->getEntityManager();
        $articleTagRep      = $em->getRepository('KsActivityBundle:ArticleTag');
        $user               = $this->get('security.context')->getToken()->getUser();
        
        $article      = new \Ks\ActivityBundle\Entity\Article($user);
        $articleForm  = $this->createForm(new \Ks\ActivityBundle\Form\ArticleType(), $article);
        
        $categories = $articleTagRep->findBy(array("isCategory" => true));
 
        return $this->render('KsActivityBundle:Article:article_new.html.twig', array(
            'articleForm'   => $articleForm->createView(),
            'categories'    => $categories
        ));
    }
    
    /**
     * Displays a form to create a new Article entity.
     *
     * @Route("/getSimilaryArticles", name="ksArticle_getSimilaryArticles")
     */
    public function getSimilaryArticlesAction()
    {              
        $em                 = $this->getDoctrine()->getEntityManager();
        $articleRep         = $em->getRepository('KsActivityBundle:Article');
        
        $request            = $this->getRequest();
        
        $parameters         = $request->request->all();
        
        $responseDatas = array();
        $articles = array();
        
        if ( ! isset( $parameters["articleLabel"] ) || empty( $parameters["articleLabel"] ) ) {
            $articles = $articleRep->findAll();
            $responseDatas['response'] = -1;
            $responseDatas['errorMessage'] = "Impossible de récupérer les articles similaires";
        } else {
            $articleLabel               = $parameters["articleLabel"];
            $articles                   = $articleRep->findBySimilarityLabel( $articleLabel );
            //$articles = $articleRep->findAll();
            $responseDatas['response']  = 1;
            
            $responseDatas['articleListHtml'] = $this->render('KsActivityBundle:Article:_article_list.html.twig', array(
                'articles'        => $articles
            ))->getContent();
        }

        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }

    /**
     * Creates a new Article entity.
     *
     * @Route("/create/{categoryId}", name="ksArticle_create", options={"expose"=true})
     * @Method("post")
     * @Template("KsActivityBundle:Article:new.html.twig")
     */
    public function createAction($categoryId)
    {
        $em                         = $this->getDoctrine()->getEntityManager();
        $request                    = $this->getRequest();
        $activityRep                = $em->getRepository('KsActivityBundle:Activity');
        $articleRep                 = $em->getRepository('KsActivityBundle:Article');
        $articleTagRep              = $em->getRepository('KsActivityBundle:ArticleTag');
        $showcaseExposesTrophiesRep = $em->getRepository('KsTrophyBundle:ShowcaseExposesTrophies');
        $typeEventRep               = $em->getRepository('KsEventBundle:TypeEvent');
        $agendaRep                  = $em->getRepository('KsAgendaBundle:Agenda');
        $equipmentRep               = $em->getRepository('KsUserBundle:Equipment');
        $coachingPlanRep            = $em->getRepository('KsCoachingBundle:CoachingPlan');
        $user                       = $this->get('security.context')->getToken()->getUser();
        
        //appels aux services
        $trophyService          = $this->get('ks_trophy.trophyService');
        
        $categoryTag =  $articleTagRep->find($categoryId);
        
        if( is_object($categoryTag )) {
            
            $article = new \Ks\ActivityBundle\Entity\Article($user);
            $article->setCategoryTag($categoryTag);
            $articleForm  = $this->createForm(new \Ks\ActivityBundle\Form\ArticleType(), $article);
            
            if ($categoryTag->getLabel() == "Matériel") {
                if ($request->getMethod() === 'POST') {
                    $articleForm->bindRequest($request);
                    if ($articleForm->isValid()) {
                        $type = $_POST['selectedType_fromMenu'];
                        $brand = $_POST['selectedBrand_fromMenu'];
                    }
                }
            }
            else if ($categoryTag->getLabel() == "Programme Entrainement") {
                if ($request->getMethod() === 'POST') {
                    $articleForm  = $this->createForm(new \Ks\ActivityBundle\Form\ArticleType('withTrainingPlan'), $article);
                    $articleForm->bindRequest($request);
                    if ($articleForm->isValid()) {
                        $planId = $_POST['planId'];
                    }
                }
            }
            
            // On crée le gestionnaire pour ce formulaire, avec les outils dont il a besoin
            $formHandler = new \Ks\ActivityBundle\Form\ArticleHandler($articleForm, $request, $em);

            $responseDatas = $formHandler->process();

            //Si l'activité a été publié
            if($responseDatas['response'] == 1) {
                //On abonne l'utilisateur à l'activité
                $activityRep->subscribeOnActivity($responseDatas['article'], $user);

                if ( ! $categoryTag) {
                    //$tagId = $tag->getId();
                    throw $this->createNotFoundException('Impossible de trouver la catégorie ' . $categoryId . '.');
                } else {
                    if ( $categoryTag->getLabel() == "Evénement Sportif" ) {
                        $eventName              = $responseDatas['article']->getLabel();

                        $event = $articleRep->createWikisportEvent( $eventName );
                        $event->setIsAllDay("1");
                        $event->setIsPublic("1");
                        $event->setTypeEvent($em->getRepository('KsEventBundle:TypeEvent')->find(2));
                        $responseDatas['article']->setEvent($event);
                        $em->persist($responseDatas['article']);
                        $em->flush();
                    }
                    if ( $categoryTag->getLabel() == "Matériel" ) {
                        //On doit créer un équipment pour le user qui crée l'article
                        $newEquipment = new \Ks\UserBundle\Entity\Equipment();
                        
                        $newEquipment->setUser($user);
                        $newEquipment->setBrand($brand);
                        $newEquipment->setName($responseDatas['article']->getLabel());
                        $equipmentType = $em->getRepository('KsUserBundle:EquipmentType')->find($type);
                        $newEquipment->setType($equipmentType);
                        $newEquipment->setActivity($responseDatas['article']);
                        $newEquipment->setIsByDefault(true);

                        $responseDatas['article']->setBrand($brand);
                        $responseDatas['article']->setEquipmentType($equipmentType);
                        
                        $em->persist($newEquipment);
                        $em->persist($responseDatas['article']);
                        $em->flush();
                    }
                    if ( $categoryTag->getLabel() == "Programme Entrainement" ) {
                        $plan = $coachingPlanRep->find($planId);
                        $responseDatas['article']->setCoachingPlan($plan);
                        $responseDatas['article']->setIsPublic(false);
                        $em->persist($responseDatas['article']);
                        $em->flush();
                    }
                }

                //Tableau qui contient tous les éléments de la modification
                $modification = array(
                    "title"         => base64_encode( $responseDatas['article']->getLabel() ),
                    "description"   => "",
                    "elements"      => array(),
                    "photos"        => array(),
                    "tags"          => array($categoryId),
                    "trainingPlan"  => array()
                );

                //Tableau qui dira si les choses ont changés
                $thingsWereChanged = array(
                    "title"         => false,
                    "description"   => false,
                    "elements"      => false,
                    "photos"        => false,
                    "tags"          => false,
                    "trainingPlan"  => false
                );

                //On enregistre les modifications sur le contenu
                $articleRep->modificationOnArticle($responseDatas['article'], $user, json_encode($modification/*, JSON_UNESCAPED_UNICODE*/), $thingsWereChanged);

                //$responseDatas['label'] = $responseDatas['article']->getLabel();
                $responseDatas['article'] = array(
                    "id" => $responseDatas['article']->getId(),
                    "label" => $responseDatas['article']->getLabel(),
                );
                /*$responseDatas['contentUpdateFormHtml'] = $this->render('KsActivityBundle:Article:_article_contentUpdateForm.html.twig', array(
                    'article'        => $responseDatas['article'],
                    'articleElements'      => array(),
                ))->getContent();*/

                ////$trophyService->beginOrContinueToWinTrophy("prem_article", $user);
            }
        } else {
            $responseDatas["response"] = -1;
            $responseDatas["errorMessage"] = "Impossible de trouver la catégory de l'article";
            
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response;    
    }
    
    /**
     * Pour récupérer le formulaire de modification d'un article
     *
     * @Route("/getContentUpdateForm/{articleId}", name="ksArticle_getContentUpdateForm", options={"expose"=true})
     */
    public function getContentUpdateFormAction($articleId)
    {
        $em       = $this->getDoctrine()->getEntityManager();

        $user               = $this->get('security.context')->getToken()->getUser();
        $articleRep   = $em->getRepository('KsActivityBundle:Article');
        $modificationsRep  = $em->getRepository('KsActivityBundle:UserModifiesArticle');
        //$article      = new \Ks\ActivityBundle\Entity\Article($user);
        //$articleForm  = $this->createForm(new \Ks\ActivityBundle\Form\ArticleType(), $article);
        
        $article      = $articleRep->find($articleId);

        if (!$article) {
            throw $this->createNotFoundException('Unable to find Article entity.');
        }

        $lastArticleModifications = $modificationsRep->getLastModification($article);
        
        $articleContent = json_decode($lastArticleModifications->getContent(), true);

        $contentUpdateFormHtml = $this->render('KsActivityBundle:Article:_article_contentUpdateForm.html.twig', array(
            'article'        => $article,
            'articleElements'      => $articleContent["elements"],
        ))->getContent();
        
        $response = new Response($contentUpdateFormHtml);
        //$response->headers->set('Content-Type', 'application/json'); 

        return $response;    
        //return $contentUpdateFormHtml;
    }
    
    /**
     * Pour modifier
     *
     * @Route("/updateContent/{articleId}", name="ksArticle_content_update", options={"expose"=true})
     * @Method("POST") 
     */
    public function updateContentAction($articleId)
    {
        $em                         = $this->getDoctrine()->getEntityManager();
        $request                    = $this->get('request');
        $user                       = $this->get('security.context')->getToken()->getUser();
        $articleRep                 = $em->getRepository('KsActivityBundle:Article');
        $modificationsRep           = $em->getRepository('KsActivityBundle:UserModifiesArticle');
        $activityRep                = $em->getRepository('KsActivityBundle:Activity');
        $eventRep                   = $em->getRepository('KsEventBundle:Event');
        $gpxRep                     = $em->getRepository('KsActivityBundle:Gpx');
        //$articleTagRep              = $em->getRepository('KsActivityBundle:ArticleTag');
        $showcaseExposesTrophiesRep = $em->getRepository('KsTrophyBundle:ShowcaseExposesTrophies');
        
        
        //appels aux services
        $trophyService          = $this->get('ks_trophy.trophyService');
        $notificationService    = $this->get('ks_notification.notificationService');
        
        $article      = $articleRep->find($articleId);
        $activity     = $activityRep->find($articleId);

        if (!$article) {
            throw $this->createNotFoundException('Unable to find Article entity.');
        }
        
        $responseDatas = array();
        
        $newModifs = false;
        
        //Tableau qui dira si les choses ont changés
        $thingsWereChanged = array(
            "title"         => false,
            "description"   => false,
            "elements"      => false,
            "photos"        => false,
            "tags"          => false,
            "trainingPlan"  => false
        );
        
        $parameters = $request->request->all();
        $articleTitle = isset( $parameters["articleTitle"] ) ? $parameters["articleTitle"] : "" ;
        $articleElements = isset( $parameters["articleElements"] ) ? $parameters["articleElements"] : array() ;
        $articleUploadedPhotos = isset( $parameters["articleUploadedPhotos"] ) ? $parameters["articleUploadedPhotos"] : array() ;
        $articleTagsId = isset( $parameters["articleTagsId"] ) ? $parameters["articleTagsId"] : array() ;
        $articlePhotosToDelete = isset( $parameters["articlePhotosToDelete"] ) ? $parameters["articlePhotosToDelete"] : array() ;
        $articleDescription = isset( $parameters["articleDescription"] ) ? $parameters["articleDescription"] : "" ;
        $articleTrainingPlan = isset( $parameters["articleTrainingPlan"] ) ? $parameters["articleTrainingPlan"] : array() ;
        $articleTrainingPlan = isset( $parameters["articleTrainingPlan"] ) ? $parameters["articleTrainingPlan"] : array() ;
        $articleGPXUploaded = isset( $parameters["articleGPXUploaded"] ) ? $parameters["articleGPXUploaded"] : array() ;
        $eventDate = isset( $parameters["eventDate"] ) ? $parameters["eventDate"] : "";
        $eventStart = isset( $parameters["eventStart"] ) ? $parameters["eventStart"] : "";
        $eventPlace = isset( $parameters["eventPlace"] ) ? $parameters["eventPlace"] : "";
        $distance = isset( $parameters["distance"] ) ? $parameters["distance"] : 0;
        $elevationGain = isset( $parameters["elevationGain"] ) ? $parameters["elevationGain"] : 0;
        $elevationLost = isset( $parameters["elevationLost"] ) ? $parameters["elevationLost"] : 0;
        $isPublic = isset( $parameters["isPublic"] ) ? $parameters["isPublic"] : true;
        $checkboxSRTM = isset( $parameters["checkboxSRTM"] ) ? $parameters["checkboxSRTM"] : false;
        $fullAddress = isset( $parameters["fullAddress"] ) ? $parameters["fullAddress"] : "";
        $countryCode = isset( $parameters["countryCode"] ) ? $parameters["countryCode"] : "";
        $countryLabel = isset( $parameters["countryLabel"] ) ? $parameters["countryLabel"] : "";
        $regionCode = isset( $parameters["regionCode"] ) ? $parameters["regionCode"] : "";
        $regionLabel = isset( $parameters["regionLabel"] ) ? $parameters["regionLabel"] : "";
        $countyCode = isset( $parameters["countyCode"] ) ? $parameters["countyCode"] : "";
        $countyLabel = isset( $parameters["countyLabel"] ) ? $parameters["countyLabel"] : "";
        $townCode = isset( $parameters["townCode"] ) ? $parameters["townCode"] : "";
        $townLabel = isset( $parameters["townLabel"] ) ? $parameters["townLabel"] : "";
        $longitude = isset( $parameters["longitude"] ) ? $parameters["longitude"] : "";
        $latitude = isset( $parameters["latitude"] ) ? $parameters["latitude"] : "";
        $sportId = isset( $parameters["sportId"] ) ? $parameters["sportId"] : "";
        
        $newModifs = true; //Pour permette la mise à jour de l'article
        //Cas usuel pour tout type d'article : sauvegarde du caractère public/privé de l'article 
        
        $activity->setIsPublic($isPublic == "true" ? true : false);
        
        $em->persist($activity);
        $em->flush();
        
        //Cas d'un article de type Evénement sportif
        if ($activity->getCategoryTag()->getId() == '2') {
            //Sauvegarde du sport
            $activity->setSport($em->getRepository('KsActivityBundle:Sport')->find($sportId));
            $em->persist($activity);
            $em->flush();
            
            // Sauvegarde du lieu si user en a choisi un
            if ($fullAddress != "") {
                $place = $activity->getPlace();
                if ( $place == null) {
                    $place = new \Ks\EventBundle\Entity\Place();
                }

                $place->setFullAdress($fullAddress);
                $place->setCountryCode($countryCode);
                $place->setCountryLabel($countryLabel);
                $place->setRegionCode($regionCode);
                $place->setRegionLabel($regionLabel);
                $place->setCountyCode($countyCode);
                $place->setCountyLabel($countyLabel);
                $place->setTownCode($townCode);
                $place->setTownLabel($townLabel);
                $place->setLongitude($longitude);
                $place->setLatitude($latitude);

                $activity->setPlace($place);

                $em->persist($place);
                $em->persist($activity);
                $em->flush();
            }

            //Cas d'un article de type Evénement sportif : traitement du gpx si sélectionné sur l'article
            $issetGPX =false;
            foreach( $articleGPXUploaded as $key => $uploadedGPX ) {
                $issetGPX =true;
                //Tout d'abord on supprime le gpx déjà existant si besoin (sinon erreur integrity constraint lors du persist du nouveau)
                $gpxArray = $gpxRep->getGpxByActivity($articleId);
                if (isset($gpxArray[0]['id'])) {
                    $oldGpx = $gpxRep->find($gpxArray[0]['id']);
                    $em->remove($oldGpx);
                    $em->flush();
                }

                $gpx = new \Ks\ActivityBundle\Entity\Gpx();
                $gpx->setUploadedBy($user->getId());
                $gpx->setUploadedAt(new \DateTime("now"));
                $gpx->setName($uploadedGPX);
                $gpx->setActivity($article);

                $em->persist($gpx);
                $importService = $this->container->get('ks_activity.importActivityService');
                $gpxPath    = $this->container->get('kernel')->getRootdir()
                    .DIRECTORY_SEPARATOR.'..'
                    .DIRECTORY_SEPARATOR.'web'
                    .DIRECTORY_SEPARATOR.'uploads'
                    .DIRECTORY_SEPARATOR.'gpx'
                    .DIRECTORY_SEPARATOR.$gpx->getName();

                list($activityDatas, $error) = $importService->buildJsonToSave($user, array('fileName' => $gpxPath), 'gpx', $checkboxSRTM);

                //Ajout du trackingDatas à l'activité correspondante
                $activity->setTrackingDatas($activityDatas);
                $activity->setDistance($activityDatas['info']['distance']);
                $activity->setElevationGain($activityDatas['info']['D+']);
                $activity->setElevationLost($activityDatas['info']['D-']);
                
                $firstWaypoint = $importService->getFirstWaypointNotEmpty($activityDatas);
                if ($firstWaypoint != null) {
                    $activity->setPlace($importService->findPlace($firstWaypoint["lat"], $firstWaypoint["lon"])
                    );
                }
                
                $em->persist($activity);
                $em->flush();
            }

            //Cas d'un article de type Evénement sportif : traitement des données liées à l'event (date, start)
            $event = $activity->getEvent();
            if ( $event != null) {
                $newModifs = true; //Pour permette la mise à jour de l'article
                $split = preg_split("|/|",$eventDate);
                $day    = $split[0];
                $month  = $split[1];
                $year   = $split[2];

                $eventStartDateTime = new \DateTime("$year-$month-$day $eventStart:00");
                //var_dump($eventStartDateTime);

                $event->setStartDate($eventStartDateTime);
                $event->setEndDate($eventStartDateTime);
                $event->setSport($em->getRepository('KsActivityBundle:Sport')->find($sportId));
                $em->persist($event);
                $em->flush();
            }
        
            //Cas d'un article de type Evénement sportif : sauvegarde de la distance, D+/D-
            if ($issetGPX) {
                //Fichier GPX chargé, on garde celles du GPX
            }
            else {
                //Sinon on prend les données affichées
                $activity->setDistance($distance);
                $activity->setElevationGain($elevationGain);
                $activity->setElevationLost($elevationLost);
                $em->persist($activity);
                $em->flush();
            }
        }
        
        //Sauvegarde des données communes à tous les articles (quelque soit le type)
        $articleTitle = base64_encode($articleTitle);
        
        //On encode la description
        $articleDescription = base64_encode($articleDescription);
        
        //On encode en utf8 tout le tableau d'éléments qui contient du texte ou un tableau
        foreach ( $articleElements as $elementKey => $element ) {
            $articleElements[$elementKey]["title"] = base64_encode($element["title"]);
            $tablesOrParagraphs = array();
            foreach( $element["content"] as $tableOrParagraph ) {
                $contentSubtitle = isset( $tableOrParagraph["subtitle"] ) ? base64_encode($tableOrParagraph["subtitle"]) : "";
                $type = $tableOrParagraph["type"];
                if( $type == "table" ) {
                    $content = array(
                        "head" => array(),
                        "body" => array()
                    );
                    
                    //On encode l'entête
                    if ( isset( $tableOrParagraph["content"]["head"] ) ) {
                        foreach( $tableOrParagraph["content"]["head"] as $iRow => $row ) {
                            foreach( $row as $column ) {
                                $content["head"][$iRow][] = base64_encode( $column );
                            }
                        }
                    }
                    
                    //On encode le reste du tableau
                    if ( isset( $tableOrParagraph["content"]["body"] ) ) {
                        foreach( $tableOrParagraph["content"]["body"] as $iRow => $row ) {
                            foreach( $row as $column ) {
                                $content["body"][$iRow][] = base64_encode( $column );
                            }
                        }
                    }
                } else {
                    $content = isset( $tableOrParagraph["content"] ) ? base64_encode($tableOrParagraph["content"]) : "";
                }
                
                $tablesOrParagraphs[] = array(
                    "type"      => $type,
                    "subtitle"  => $contentSubtitle,
                    "content"   => $content
                );
                //$paragraphs[] = base64_encode($paragraph);
            }
            $articleElements[$elementKey]["content"] = $tablesOrParagraphs;
        }
        
         //On encode en utf8 le plan d'entrainement s'il y en a un
        if( ! empty( $articleTrainingPlan ) ) {
            foreach ( $articleTrainingPlan as $numWeek => $week ) {
                foreach ( $week as $numSession => $session ) {
                     $articleTrainingPlan[$numWeek][$numSession]["description"] = base64_encode($session["description"]);
                }  
            }
        }
        
        //var_dump($articleElements);
        //On récupère la dernière version de l'article
        $lastArticleModifications = $modificationsRep->getLastModification($article);
        //var_dump($lastArticleModifications);
        if ( ! empty($lastArticleModifications)) {
            $articleContent = json_decode($lastArticleModifications->getContent(), true);
            
            //var_dump($articleContent["elements"]);
            //var_dump($articleElements);
            
            if (isset($articleContent["title"]) && $articleContent["title"] !== $articleTitle ) {
                $thingsWereChanged["title"] = true;
                $newModifs = true;
            }
            
            //On compare les éléments et les tags de l'ancienne version avec la nouvelle pour savoir s'ils ont été modifiés
            //TRUE si $a et $b contiennent les mêmes paires clés / valeurs dans le même ordre et du même type.
            if (isset($articleContent["elements"]) && $articleContent["elements"] !== $articleElements) {
                $thingsWereChanged["elements"] = true;
                $newModifs = true;
            }
            
            if (isset($articleContent["tags"]) && $articleContent["tags"] !== $articleTagsId) {
                $thingsWereChanged["tags"] = true;
                $newModifs = true;
            }
            
            if (isset($articleContent["description"]) && $articleContent["description"] !== $articleDescription) {
                $thingsWereChanged["description"] = true;
                $newModifs = true;
            }
            
            if (isset($articleContent["trainingPlan"]) && $articleContent["trainingPlan"] !== $articleTrainingPlan) {
                $thingsWereChanged["trainingPlan"] = true;
                $newModifs = true;
            }
        } else {
            $articleContent = array(
                "title"         => "",
                "description"   => "",
                "elements"      => array(),
                "photos"        => array(),
                "tags"          => array(),
                "trainingPlan"  => array()
            );
            
            if( !empty($articleTitle) ) {
                $thingsWereChanged["title"] = true;
                $newModifs = true;
            }
            
            if( !empty($articleElements) ) {
                $thingsWereChanged["elements"] = true;
                $newModifs = true;
            }
            
            if ( !empty($articleTagsId) ) {
                $thingsWereChanged["tags"] = true;
                $newModifs = true;
            }
            
            if ( !empty($articleDescription) ) {
                $thingsWereChanged["description"] = true;
                $newModifs = true;
            }
            
            if ( !empty($articleTrainingPlan) ) {
                $thingsWereChanged["trainingPlan"] = true;
                $newModifs = true;
            }
        }
        
        //On ajoutera les nouvelles photos avec les anciennes
        $articlePhotos = isset( $articleContent["photos"] ) ? $articleContent["photos"] : array();
 
        //On supprime les photos que l'utilisateur souhaite
        foreach( $articlePhotos as $key => $photo ) {
             foreach ( $articlePhotosToDelete as $photoToDelete) {
                if( $photo["path"] == $photoToDelete ) {
                    unset($articlePhotos[$key]);
                    $thingsWereChanged["photos"] = true;
                    $newModifs = true;
                }
            }
            
        }
       
        //On réinitialise les tags de l'articles
        //$articleTagRep->resetArticleTags($article, $articleTagsId);
        //var_dump($parameters);
        //On déplace les photos téléchargés dans un dossier spécial
        //$uploadDirRelative = $this->container->get('templating.helper.assets')->getUrl('uploads');
        //$uploadDirAbsolute = $_SERVER['DOCUMENT_ROOT'] . $uploadDirRelative ."/wiki/";
        $uploadDirAbsolute = dirname($_SERVER['SCRIPT_FILENAME']). '/uploads/wiki/';
        //$imgDirRelative = $this->container->get('templating.helper.assets')->getUrl('img');
        //$articleDirAbsolute = $_SERVER['DOCUMENT_ROOT'] . $imgDirRelative . "/wiki/" . $articleId . "/";
        $articlesDirAbsolute = dirname($_SERVER['SCRIPT_FILENAME']). '/img/wiki/';
        $articleDirAbsolute = $articlesDirAbsolute.$articleId.'/';
        
        if (! is_dir( $articlesDirAbsolute ) ) mkdir($articlesDirAbsolute);
        if (! is_dir( $articleDirAbsolute ) ) mkdir($articleDirAbsolute);
        
        $articleOriginalPhotosDirAbsolute = $articleDirAbsolute . 'original/';
        if (! is_dir( $articleOriginalPhotosDirAbsolute ) ) mkdir($articleOriginalPhotosDirAbsolute);

        $articleThumbnailPhotosDirAbsolute = $articleDirAbsolute . 'thumbnail/';
        if (! is_dir( $articleThumbnailPhotosDirAbsolute ) ) mkdir($articleThumbnailPhotosDirAbsolute);
        
        
        $photoName = 0;
        foreach( $articleUploadedPhotos as $key => $uploadedPhoto ) {
            //On récupère l'extension de la photo
            $ext = explode('.', $uploadedPhoto);
            $ext = array_pop($ext);
            $ext = "." . $ext;
            
            //On lui trouve un nom qui n'existe pas.
            while( file_exists($articleOriginalPhotosDirAbsolute.$photoName.$ext) ) $photoName++;
            
            //On la déplace les photos originales et redimentionnés
            $renameOriginale = rename( $uploadDirAbsolute."original/"  . $uploadedPhoto, $articleOriginalPhotosDirAbsolute.$photoName.$ext );
            $renameThumbnail = rename( $uploadDirAbsolute."thumbnail/" . $uploadedPhoto, $articleThumbnailPhotosDirAbsolute.$photoName.$ext );
            
            //On la déplace
            if( $renameOriginale && $renameThumbnail ){
                $movePhotoResponse = 1;

                $articlePhotos[] = array(
                    "title"         => $photoName,
                    "description"   => "",
                    "path"          => $photoName.$ext
                );
                
                $thingsWereChanged["photos"] = true;
                $newModifs = true;
            } else {
                $movePhotoResponse = -1;
            }
            
            $responseDatas["movePhotosResponse"][$key] = $movePhotoResponse;
        }
        
        //Si au moins quelque chose a été modifié
        if ( $newModifs ) {
        
            //Tableau qui contient tout les éléments de la modification
            $modification = array(
                "title"         => $articleTitle,
                "description"   => $articleDescription,
                "elements"      => $articleElements,
                "photos"        => $articlePhotos,
                "tags"          => $articleTagsId,
                "trainingPlan"  => $articleTrainingPlan
            );
            
            //On abonne l'utilisateur à l'activité s'il ne l'a jamais été
            if ( $activityRep->isNotSubscribed($article, $user) && ! $activityRep->hasNotUnsubscribed($article, $user) ) {
                $activityRep->subscribeOnActivity($article, $user);
            }

            //On enregistre les modifications sur le contenu
            $modif = $articleRep->modificationOnArticle($article, $user, json_encode($modification), $thingsWereChanged);



            $responseDatas['photosHtml'] = $this->render('KsActivityBundle:Article:_article_photos_edit.html.twig', array(
                'article'   => $article,
                'photos'    => $articlePhotos,
            ))->getContent();
            
            $responseDatas['code'] = 1;
            
            //Création d'une notification
            $notificationType_name = "edit";
            $notificationType = $em->getRepository('KsNotificationBundle:NotificationType')->findOneByName($notificationType_name);

            if (!$notificationType) {
                throw $this->createNotFoundException('Impossible de trouver le type de notification ' . $notificationType_name . '.');
            }
            
            $activityRep->collaborationOnArticle($article, $user, $notificationType);
            
            //On envoie une notification à tout les abonnés de l'article
            foreach($article->getSubscribers() as $key => $activityHasSubscribers) {
                $subscriber = $activityHasSubscribers->getSubscriber();

                //S'il ne s'est pas déabonné de l'activité
                if ($activityRep->hasNotUnsubscribed($article, $subscriber)) {

                    //Si l'abonné n'est pas lui même et qu'il n'a pas posté l'activité
                    if($subscriber != $user) {
                        $message =  $user->getUsername() . " a collaboré à l'article " . base64_decode( $articleTitle );
                        $notificationService->sendNotification($article, $user, $subscriber, $notificationType_name, $message);  
                    }
                }
            }
            
            //on coche l'action correspondante dans la checklist
            $em->getRepository('KsUserBundle:ChecklistAction')->checkParticipateArticle($user->getId());
            
            //Gain possibles de badges en éditant un article
            $articlesTrohiesCode = array(
                "collab_1_article",
                "collab_3_articles",
                "collab_5_articles",
                "collab_10_articles",
                "collab_30_articles",
                "collab_50_articles", 
            );
            
            foreach( $articlesTrohiesCode as $articleTrohyCode ) {
                //$trophyService->beginOrContinueToWinTrophy( $articleTrohyCode, $user);   
            }
            
        } else {
            $responseDatas['code'] = -2;
            $responseDatas['errorMessage'] = "La modification n'a pas été enregistrée. Vous n'avez rien modifié.";
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    /**
     * Retourne les différences entre deux modifications de contenu
     *
     * @Route("/showDifferences/{differencesType}_{articleId}_{modificationId}", name="ksArticle_showDifferences", options={"expose"=true})
     * @Template()
     */
    public function showDifferencesAction($differencesType, $articleId, $modificationId )
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $request            = $this->getRequest();
        $user               = $this->get('security.context')->getToken()->getUser();
        $articleRep         = $em->getRepository('KsActivityBundle:Article');
        $articleTagRep      = $em->getRepository('KsActivityBundle:ArticleTag');
        $modificationsRep   = $em->getRepository('KsActivityBundle:UserModifiesArticle');
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        
        $responseDatas = array();
        
        $actualArticle             = $modificationsRep->find($articleId);
        
        if (!$actualArticle) {
            throw $this->createNotFoundException('Unable to find UserModifiesSC entity.');
        }
        
        $actualArticleContent       = json_decode($actualArticle->getContent(), true);
        
        $versionArticle             = $modificationsRep->find($modificationId);
        
        if (!$versionArticle) {
            throw $this->createNotFoundException('Unable to find UserModifiesSC entity.');
        }
        
        $versionArticleContent     = json_decode($versionArticle->getContent(), true);
        
        $differences = array();
        //print $this->arr_diff( $lastUserModifiesSC->getContent(), $userModifiesArticle->getContent() ,1);
        
        $granularities = array(
            "Paragraphe" => \Ks\ActivityBundle\Entity\FineDiff::$paragraphGranularity,
            "Phrase"  => \Ks\ActivityBundle\Entity\FineDiff::$sentenceGranularity,
            "Mot"      => \Ks\ActivityBundle\Entity\FineDiff::$wordGranularity,
            //"character" => \Ks\ActivityBundle\Entity\FineDiff::$characterGranularity,
            //"text"      => \Ks\ActivityBundle\Entity\FineDiff::$textStack
        );
        
        switch( $differencesType ) {
            case "elements" :
                $nbElementsActual = count($actualArticleContent["elements"]);
                $nbElementsVersion = count($versionArticleContent["elements"]);

                foreach( $granularities as $granularityName => $granularity ) {
                    $differences[$granularityName] = array();
                    for( $i = 0; $i < max($nbElementsActual, $nbElementsVersion); $i++) {                              

                        $nbParagraphsActual = count($actualArticleContent["elements"]);
                        $nbParagraphsVersion = count($versionArticleContent["elements"]);

                        $elementActual  = isset( $actualArticleContent["elements"][$i] ) ? $actualArticleContent["elements"][$i] : array();
                        $elementVersion = isset( $versionArticleContent["elements"][$i] ) ? $versionArticleContent["elements"][$i] : array();

                        $elementTitleActual    = isset( $elementActual["title"] ) ? base64_decode($elementActual["title"]) : "" ; 
                        $elementTitleVersion   = isset( $elementVersion["title"] ) ? base64_decode($elementVersion["title"]) : "" ;

                        $fineDiff = new \Ks\ActivityBundle\Entity\FineDiff($elementTitleVersion, $elementTitleActual, $granularity);
                        $differences[$granularityName][$i]["title"] = $fineDiff->renderDiffToHTML();

                        for( $j = 0; $j <= max($nbParagraphsActual, $nbParagraphsVersion); $j++) {
                            $subtitleActual    = isset( $elementActual["paragraphs"][$j]["subtitle"] ) ? base64_decode($elementActual["paragraphs"][$j]["subtitle"] ) : "" ; 
                            $subtitleVersion   = isset( $elementVersion["paragraphs"][$j]["subtitle"] ) ? base64_decode($elementVersion["paragraphs"][$j]["subtitle"] ) : "" ;
                            //var_dump($i." ".$j." actual : ".$paragraphActual);
                            //var_dump($i." ".$j." version : ".$paragraphVersion);
                            $fineDiff = new \Ks\ActivityBundle\Entity\FineDiff($subtitleActual, $subtitleActual, $granularity);
                            $differences[$granularityName][$i]["paragraphs"][$j]["subtitle"] = $fineDiff->renderDiffToHTML();
                            
                            $paragraphActual    = isset( $elementActual["paragraphs"][$j]["paragraph"] ) ? base64_decode($elementActual["paragraphs"][$j]["paragraph"] ) : "" ; 
                            $paragraphVersion   = isset( $elementVersion["paragraphs"][$j]["paragraph"] ) ? base64_decode($elementVersion["paragraphs"][$j]["paragraph"] ) : "" ;
                            //var_dump($i." ".$j." actual : ".$paragraphActual);
                            //var_dump($i." ".$j." version : ".$paragraphVersion);
                            $fineDiff = new \Ks\ActivityBundle\Entity\FineDiff(strip_tags($paragraphVersion), strip_tags($paragraphActual), $granularity);
                            $differences[$granularityName][$i]["paragraphs"][$j]["paragraph"] = $fineDiff->renderDiffToHTML();
                            //var_dump($i." ".$j." dif : ".$fineDiff->renderDiffToHTML());                   
                            //var_dump("");
                        }
                    }
                }
                
                $responseDatas['differencesHtml'] = $this->render('KsActivityBundle:Article:_article_differencesBetweenElements.html.twig', array(
                    'differences'        => $differences,
                    'granularitiesNames' => array_keys($granularities),
                    'article'            => $versionArticle
                ))->getContent();
                
                break;
                
            case "descriptions" :
                foreach( $granularities as $granularityName => $granularity ) {
                    $descriptionActual  = isset( $actualArticleContent["description"] ) ? base64_decode($actualArticleContent["description"]) : "";
                    $descriptionVersion = isset( $versionArticleContent["description"] ) ? base64_decode($versionArticleContent["description"]) : "";
                    
                    $fineDiff = new \Ks\ActivityBundle\Entity\FineDiff($descriptionVersion, $descriptionActual, $granularity);
                    
                    $differences[$granularityName] = $fineDiff->renderDiffToHTML();
                }
                
                $responseDatas['differencesHtml'] = $this->render('KsActivityBundle:Article:_article_differencesBetweenDescriptions.html.twig', array(
                    'differences'        => $differences,
                    'granularitiesNames' => array_keys($granularities),
                    'article'            => $versionArticle
                ))->getContent();
                break;
                
            case "tags" :
                $tags = $articleTagRep->findAll();  
                $responseDatas['differencesHtml'] = $this->render('KsActivityBundle:Article:_article_differencesBetweenTags.html.twig', array(
                    'actualArticleTags'     => $actualArticleContent["tags"],
                    'versionArticleTags'    => $versionArticleContent["tags"],
                    'tags'                  => $tags
                ))->getContent();
                break;
            
           case "photos":
                $responseDatas['differencesHtml'] = $this->render('KsActivityBundle:Article:_article_differencesBetweenPhotos.html.twig', array(
                    'actualArticlePhotos'     => $actualArticleContent["photos"],
                    'versionArticlePhotos'    => $versionArticleContent["photos"],
                    'articleId'               => $articleId
                ))->getContent();
                break;
        }
       

        

        
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response;   
    }

    /**
     * Displays a form to edit an existing Article entity.
     *
     * @Route("/{articleId}/edit", name="ksArticle_edit", options={"expose"=true})
     * @Template()
     */
    public function editAction($articleId)
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();
        $articleRep         = $em->getRepository('KsActivityBundle:Article');
        $modificationsRep   = $em->getRepository('KsActivityBundle:UserModifiesArticle');
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        $articleTagRep      = $em->getRepository('KsActivityBundle:ArticleTag');
        $article            = $articleRep->find($articleId);

        if (!$article) {
            throw $this->createNotFoundException('Unable to find Article entity.');
        }
        $lastArticleModifications = null;//$modificationsRep->getLastModification($article);
        
        if (! empty($lastArticleModifications)) {
            $articleContent = json_decode($lastArticleModifications->getContent(), true);
            
            //Si la version n'est pas complete
            if ( ! isset($articleContent["title"]) )        $articleContent["title"]  = "";
            if ( ! isset($articleContent["description"]) )  $articleContent["description"]  = "";
            if ( ! isset($articleContent["elements"]) )     $articleContent["elements"]     = array();
            if ( ! isset($articleContent["photos"]) )       $articleContent["photos"]       = array();
            if ( ! isset($articleContent["tags"]) )         $articleContent["tags"]         = array();
            if ( ! isset($articleContent["trainingPlan"]) ) $articleContent["trainingPlan"] = array();
            
            if( isset( $articleContent["title"] )) {
                $articleContent["title"] = base64_decode($articleContent["title"]);
            } else {
                $articleContent["title"] = $article->getLabel();
            }
            
            //Si le titre n'est pas encore versionné
            if ( empty( $articleContent["title"] ) ) $articleContent["title"] = $article->getLabel();
            
            //On decode les champs de textes et les tableaux
            foreach ( $articleContent["elements"] as $elementKey => $element ) {
                $articleContent["elements"][$elementKey]["title"] = trim( base64_decode($element["title"]) );
                $tablesOrParagraphs = array();
                
                foreach( $element["content"] as $tableOrParagraph ) {
                    $contentSubtitle = isset( $tableOrParagraph["subtitle"] ) ? trim( base64_decode( $tableOrParagraph["subtitle"] ) ) : "";                   
                    $type = $tableOrParagraph["type"];
                    if( $type == "table" ) {
                        $content = array(
                            "head" => array(),
                            "body" => array()
                        );

                        //On encode l'entête
                        if ( isset( $tableOrParagraph["content"]["head"] ) ) {
                            foreach( $tableOrParagraph["content"]["head"] as $iRow => $row ) {
                                foreach( $row as $column ) {
                                    $content["head"][$iRow][] = trim( base64_decode( $column ) );
                                }
                            }
                        }

                        //On encode le reste du tableau
                        if ( isset( $tableOrParagraph["content"]["body"] ) ) {
                            foreach( $tableOrParagraph["content"]["body"] as $iRow => $row ) {
                                foreach( $row as $column ) {
                                    $content["body"][$iRow][] = trim( base64_decode( $column ) );
                                }
                            }
                        }
                    } else {
                        $content = isset( $tableOrParagraph["content"] ) ? trim( base64_decode($tableOrParagraph["content"]) ) : "";
                    }

                    $tablesOrParagraphs[] = array(
                        "type"      => $type,
                        "subtitle"  => $contentSubtitle,
                        "content"   => $content
                    );
                }
                $articleContent["elements"][$elementKey]["content"] = $tablesOrParagraphs;                     
            }
            
            $articleContent["description"] = trim( base64_decode($articleContent["description"]) );
            
            //On décode le plan d'entrainement
             //On encode en utf8 le plan d'entrainement s'il y en a un
            if( ! empty( $articleContent["trainingPlan"] ) ) {
                foreach ( $articleContent["trainingPlan"] as $numWeek => $week ) {
                    foreach ( $week as $numSession => $session ) {
                        $articleContent["trainingPlan"][$numWeek][$numSession]["description"] = trim( base64_decode($session["description"]) );
                    }  
                }
            }

        } else {
            
            $tag = $articleTagRep->findOneByLabel("Article");
            
            if ( $tag) {
                $articleContent = array(
                    "title"         => "",
                    "description"   => "",
                    "elements"      => array(),
                    "photos"        => array(),
                    "tags"          => array($tag->getId()),
                    "trainingPlan"  => array()
                );
            } else {
                $articleContent = array(
                    "title"         => "",
                    "description"   => "",
                    "elements"      => array(),
                    "photos"        => array(),
                    "tags"          => array(),
                    "trainingPlan"  => array()
                );
            } 
        }
        
        $tags = $articleTagRep->findAll();       
        $articles = $articleRep->findBy(array(
                    "categoryTag"         => 1));
        
        
       
//        if( $article->getIsBeingEdited() )  {
//        
//            $redirectionShow = false;
//            //Si l'article est en cours d'édition depuis plus de 15 minutes
//            $isBeingEditedDate = $article->getIsBeingEditedDate();
//            if( $isBeingEditedDate != null ) {
//                $timeSinceLastEdition = time() - $isBeingEditedDate->getTimestamp();
//                if ( $timeSinceLastEdition < 900 ) $redirectionShow = true;
//            } else $redirectionShow = true;
//            
//            //On sette une flash (Message qui apparaitra au chargement de la prochaine page)
//            
//            if( $redirectionShow ) {
//                $this->get('session')->setFlash('alert alert-error', 'article.flash.article_is_being_edited');
//                return new RedirectResponse($this->generateUrl('ksArticle_show', array("articleId" => $article->getId())));
//            }
//        }
        
        //On indique que l'article est en cours d'édition
//        $article->setIsBeingEditedDate(new \DateTime());
//        $article->setIsBeingEdited(true);
//        $em->persist($article);
//        $em->flush();
        
        
        //Récupération d'un formulaire pour la création de plans d'entrainement
        $trainingPlan = new \Ks\ActivityBundle\Entity\TrainingPlan();
        $trainingPlanForm  = $this->createForm(new \Ks\ActivityBundle\Form\TrainingPlanType(), $trainingPlan);
        //var_dump( $articleContent );
        
        return $this->render('KsActivityBundle:Article:article_edit.html.twig', array(
            'article'               => $article,
            'articleContent'        => $articleContent,
            //'articleForm'           => $articleForm,
            //'contentUpdateForm'     => $userModifiesArticleForm->createView(),
            'trainingPlanForm'      => $trainingPlanForm->createView(),
            'tags'                  => $tags,
            'articles'              => $articles,
        ));
    }

    /**
     * Edits an existing Article entity.
     *
     * @Route("/{id}/update", name="ksArticle_update")
     * @Method("post")
     * @Template("KsActivityBundle:Article:edit.html.twig")
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('KsActivityBundle:Article')->find($id);
        

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Article entity.');
        }

        $editForm   = $this->createForm(new ArticleType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('ksArticle_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Article entity.
     *
     * @Route("/{id}/delete", name="ksArticle_delete")
     * @Method("post")
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('KsActivityBundle:Article')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Article entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl(''));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
    
    /**
     * Pour ajouter un mot clé
     *
     * @Route("/addTag", name="ksArticle_addTag", options={"expose"=true})
     * 
     */
    public function addTagAction () {
        $em = $this->getDoctrine()->getEntityManager();
        $articleRep         = $em->getRepository('KsActivityBundle:Article');
        $articleTagRep      = $em->getRepository('KsActivityBundle:ArticleTag');
        $request    = $this->getRequest();
        
        $responseDatas = array();
        
        $parameters = $request->request->all();
        $tagName = isset( $parameters["tagName"] ) ? $parameters["tagName"] : "" ;
        
        if( isset($tagName) && !empty($tagName) ) {
            $articleTag = $articleTagRep->addTag($tagName);

            if($articleTag) {
                $responseDatas["addTagResponse"] = 1;
                $responseDatas["tag"] = array(
                    "id"    => $articleTag->getId(),
                    "label" => $articleTag->getLabel()
                );
            } else {
                $responseDatas["addTagResponse"] = -1;
            }
            
        } else {
            $responseDatas["addTagResponse"] = -1;
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 
        
        return $response;
    }
    
    /**
     * Pour ajouter un mot clé
     *
     * @Route("/anymoreBeingEdited/{articleId}", name="ksArticle_anymoreBeingEdited", options={"expose"=true})
     * 
     */
    public function anymoreBeingEditedAction ($articleId) {
        $em = $this->getDoctrine()->getEntityManager();
        $articleRep         = $em->getRepository('KsActivityBundle:Article');
        
        $responseDatas = array();
        
        $article            = $articleRep->find($articleId);

        if ( !$article ) {
            throw $this->createNotFoundException('Unable to find Article entity.');
        }
        
        //On indique que l'article n'est plus en cours d'édition
        $article->setIsBeingEdited(false);
        $em->persist($article);
        $em->flush();
        
        $isBeingEdited = $article->getIsBeingEdited();      
        
        if( ! $isBeingEdited ) {
            $responseDatas["code"] = 1;
        } else {
            $responseDatas["code"] = -1;
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 
        
        return $response;
    }
    
    /**
     * @Route("/abstractActivityLabel/{articleId}_{notificationTypeId}", name = "ksArticle_abstractActivityLabel" )
     */
    public function abstractActivityLabelAction($articleId, $notificationTypeId ) {
        $em = $this->getDoctrine()->getEntityManager();
        $articleTagRep              = $em->getRepository('KsActivityBundle:ArticleTag');
        $notificationTypeRep    = $em->getRepository('KsNotificationBundle:NotificationType');
        
        $article            = $articleRep->find($articleId);

        if ( !$article ) {
            throw $this->createNotFoundException('Unable to find Article entity.');
        }
        
        $notificationType            = $notificationTypeRep->find($notificationTypeId);

        if ( !$notificationType ) {
            throw $this->createNotFoundException('Unable to find notification type entity.');
        }
        
        $articleCategory = "";
        
        $lastArticleModifications = $modificationsRep->getLastModification( $article );
        //var_dump($lastArticleModifications);
        if ( ! empty($lastArticleModifications)) {
            $articleContent = json_decode($lastArticleModifications->getContent(), true);
            
            if (isset($articleContent["tags"]) && !empty( $articleContent["tags"] ) ) {
                foreach($articleContent["tags"] as $tagId ) {
                    $tag = $articleTagRep->find($tagId);
                    if( $tag && $tag->getIsCategory() ) {
                        $articleCategory = $tag;
                        break;
                    }
                }
            }
        }
        
        if (empty( $articleCategory ) ) {
            $articleCategory = $articleTagRep->findByLabel("Article");
        }

        return $this->render('KsActivityBundle:Article:_abstractActivity_label.html.twig', array(
            'articleCategory'           => $articleCategory,
            //'new_notifications_number'  => $new_notifications_number
        ));
    }
    
    /**
     * 
     * @Route("/participationInArticleSportingEvent/{articleId}", requirements={"articleId" = "\d+"}, name = "ksArticle_participationInArticleSportingEvent", options={"expose"=true} )
     * @param int $articleId 
     */
    public function participationInArticleSportingEventAction($articleId)
    {
        $em                         = $this->getDoctrine()->getEntityManager();
        $articleRep                 = $em->getRepository('KsActivityBundle:Article');
        $userRep                    = $em->getRepository('KsUserBundle:User');
        $userParticipatesEventRep   = $em->getRepository('KsEventBundle:UserParticipatesEvent');
        $agendaHasEventsRep         = $em->getRepository('KsAgendaBundle:AgendaHasEvents');
        $user                       = $this->get('security.context')->getToken()->getUser();
        
        $article = $articleRep->find($articleId);
        
        if (!is_object($article) ) {
            throw new AccessDeniedException("Impossible de trouver l'article " . $articleId .".");
        }
        
        $responseDatas = array();
        $responseDatas["participateResponse"] = 1;
        
        /*if( ! $userRep->alreadyParticipatesInArticleSportingEvent( $article, $user ) ) {
            $articleRep->participateInSportingEvent( $article, $user );
            
            $agenda = $user->getAgenda();
            $event  = $article->getEvent();
            
            if( is_object( $agenda ) && is_object( $event ) ) {
                if( ! $agendaHasEventsRep->eventIsInAgenda( $agenda, $event ) ) {
                    $agendaHasEventsRep->addEventToAgenda( $agenda, $event );
                } else {
                    $responseDatas["participateResponse"] = -1;
                    $responseDatas["errorMessage"] = "Cet événement est déjà dans votre agenda";
                }
            }
            
            
        }*/ 
        
        $event = $article->getEvent();
        
        if( ! $userParticipatesEventRep->userAlreadyParticipatesEvent( $event, $user ) ) {
            $userParticipatesEventRep->userParticipatesEvent( $event, $user );
            
            $agenda = $user->getAgenda();
            
            if( is_object( $agenda ) && is_object( $event ) ) {
                if( ! $agendaHasEventsRep->eventIsInAgenda( $agenda, $event ) ) {
                    $agendaHasEventsRep->addEventToAgenda( $agenda, $event );
                } else {
                    $responseDatas["participateResponse"] = -1;
                    $responseDatas["errorMessage"] = "Cet événement est déjà dans votre agenda";
                }
            }
            
            
        } else {
            $responseDatas["participateResponse"] = -1;
            $responseDatas["errorMessage"] = "Vous participez déjà à cet événement.";
        }
        
        $responseDatas["subscriptionOnArticleSportingEventLink"] = $this->render('KsActivityBundle:Article:_article_subscriptionOnSportingEventLink.html.twig', array(
            'article'        => $article,
        ))->getContent();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
    
    /**
     * 
     * @Route("/removeParticipationInArticleSportingEvent/{articleId}", requirements={"articleId" = "\d+"}, name = "ksArticle_removeParticipationInArticleSportingEvent", options={"expose"=true} )
     * @param int $articleId 
     */
    public function removeParticipationInArticleSportingEventAction($articleId)
    {
        $em                         = $this->getDoctrine()->getEntityManager();
        $articleRep                 = $em->getRepository('KsActivityBundle:Article');
        $userRep                    = $em->getRepository('KsUserBundle:User');
        $agendaHasEventsRep         = $em->getRepository('KsAgendaBundle:AgendaHasEvents');
        $userParticipatesEventRep   = $em->getRepository('KsEventBundle:UserParticipatesEvent');
        $user                       = $this->get('security.context')->getToken()->getUser();
        
        $article = $articleRep->find($articleId);
        
        if (!is_object($article) ) {
            throw new AccessDeniedException("Impossible de trouver l'article " . $articleId .".");
        }
        
        $responseDatas = array();
        $responseDatas["participateResponse"] = 1;
        
        /*if( $userRep->alreadyParticipatesInArticleSportingEvent( $article, $user ) ) {
            $articleRep->participateAnymoreInSportingEvent($article, $user);
            
            $agenda = $user->getAgenda();
            $event  = $article->getEvent();
            
            if( is_object( $agenda ) && is_object( $event ) ) {
                if( $agendaHasEventsRep->eventIsInAgenda( $agenda, $event ) ) {
                    $agendaHasEvents = $agendaHasEventsRep->findOneBy(
                        array(
                            "agenda" => $agenda->getId(), 
                            "event" => $event->getId()
                        )
                    );

                    if (!is_object($agendaHasEvents) ) {
                        throw new AccessDeniedException("Impossible de trouver la liasion agenda-événement.");
                    }
                    $agendaHasEventsRep->removeEventToAgenda( $agendaHasEvents );
                } else {
                    $responseDatas["participateResponse"] = -1;
                    $responseDatas["errorMessage"] = "Cet événement n'est pas dans votre agenda";
                }
            }
            
        } */
        $event  = $article->getEvent();
        
        if( $userParticipatesEventRep->userAlreadyParticipatesEvent( $event, $user ) ) {
            $userParticipatesEventRep->userParticipatesAnymoreEvent( $event, $user);
            
            $agenda = $user->getAgenda();
            
            if( is_object( $agenda ) && is_object( $event ) ) {
                if( $agendaHasEventsRep->eventIsInAgenda( $agenda, $event ) ) {
                    $agendaHasEvents = $agendaHasEventsRep->findOneBy(
                        array(
                            "agenda" => $agenda->getId(), 
                            "event" => $event->getId()
                        )
                    );

                    if (!is_object($agendaHasEvents) ) {
                        throw new AccessDeniedException("Impossible de trouver la liasion agenda-événement.");
                    }
                    $agendaHasEventsRep->removeEventToAgenda( $agendaHasEvents );
                } else {
                    $responseDatas["participateResponse"] = -1;
                    $responseDatas["errorMessage"] = "Cet événement n'est pas dans votre agenda";
                }
            }
            
        } else {
            $responseDatas["participateResponse"] = -1;
            $responseDatas["errorMessage"] = "Vous ne participez pas à cet événement.";
        }
        
        $responseDatas["subscriptionOnArticleSportingEventLink"] = $this->render('KsActivityBundle:Article:_article_subscriptionOnSportingEventLink.html.twig', array(
            'article'        => $article,
        ))->getContent();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
    
    /**
     * @Route("/lastModifiedArticles/{nbArticles}", requirements={"nbArticles" = "\d+"}, name = "ksArticle_lastModifiedArticles" )
     */
    public function lastModifiedArticlesAction($nbArticles)
    {    
        $em         = $this->getDoctrine()->getEntityManager();
        $articleRep = $em->getRepository('KsActivityBundle:Article');
        
        $articles = $articleRep->getLinksToLastUpdatedArticles($nbArticles);
        shuffle($articles);
        
        return $this->render('KsActivityBundle:Article:_last_modified_articles.html.twig', array(
            'articles' => $articles,
        ));
    }
}
