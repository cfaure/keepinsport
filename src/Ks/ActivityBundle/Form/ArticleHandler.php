<?php
// src/Sdz/BlogBundle/Form/ArticleHandler.php

namespace Ks\ActivityBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Ks\ActivityBundle\Entity\Article;

class ArticleHandler
{
    protected $form;
    protected $request;
    protected $em;

    public function __construct(Form $form, Request $request, EntityManager $em)
    {
        $this->form    = $form;
        $this->request = $request;
        $this->em      = $em;
    }

    public function process()
    {   
        $articleRep   = $this->em->getRepository('KsActivityBundle:Article');
        
        if ( $this->request->isXmlHttpRequest()) {
            $this->form->bindRequest($this->request);
            
            if ( $this->form->isValid() ) {

                if ( ! $articleRep->articleLabelExist( $this->form->get('label')->getData() ) ) {
                    $responseDatas = $this->onSuccess($this->form->getData());  
                } else {
                    $errors['label'] = array("Cet article existe déjà");
                    $responseDatas = $this->onError($errors);
                }
            } else {
                $errors = \Form\FormValidator::getErrorMessages($this->form);
                $responseDatas = $this->onError($errors);   
            }
        } else {
            $responseDatas = $this->onIsNotXmlHttpRequest(); 
        }

        return $responseDatas;
    }

    public function onSuccess(\Ks\ActivityBundle\Entity\Article $article)
    {
        //$label = $article->getLabel();
        
        //La première lettre doit toujours être une majuscule
        //$article->setLabel(ucfirst(strtolower($label)));
        //$article->setLabel(utf8_encode($article->getLabel()));
        $this->em->persist($article);
        $this->em->flush();

        $responseDatas = array(
            'response' => 1,
            'article'  => $article
        ); 
        
        return $responseDatas;
    }
    
    public function onError($errors)
    {
        $responseDatas = array(
            'response'   => -1,
            'errorMessage'      => "Le formulaire n'est pas valide.",
            'errors'            => $errors
        ); 
        
        return $responseDatas;
    }
    
    public function onIsNotXmlHttpRequest()
    {
        $responseDatas = array(
            'response'   => -1,
            'errorMessage'      => "La requête n'est pas une requête ajax."
        ); 
        
        return $responseDatas;
    }
}
