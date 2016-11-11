<?php
// src/Sdz/BlogBundle/Form/ArticleHandler.php

namespace Ks\ActivityBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Ks\ActivityBundle\Entity\ActivityStatus;

class CommentHandler
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
        //var_dump($this->request);
        if ( $this->request->isXmlHttpRequest()) {
            $this->form->bindRequest($this->request);

            if ( $this->form->isValid() ) {
                $responseDatas = $this->onSuccess($this->form->getData());  
            } else {
                $errors = \Form\FormValidator::getErrorMessages($this->form);
                $responseDatas = $this->onError($errors);    
            }
        } else {
            $responseDatas = $this->onIsNotXmlHttpRequest(); 
        }

        return $responseDatas;
    }

    public function onSuccess(\Ks\ActivityBundle\Entity\Comment $comment)
    {
        /*$commentWithoutHtmlTags = strip_tags($comment->getComment());
        $comment->setComment($commentWithoutHtmlTags);*/
        
        $this->em->persist($comment);
        $this->em->flush();
        
        $commentPublished = array(
            "id"            => $comment->getId(),
            "comment"   => $comment->getComment(),
            "user"          => array(
                    "id"            => $comment->getUser()->getId(),
                    "username"      => $comment->getUser()->getUsername(),
                    "leagueLevel"   => array("label" => $comment->getUser()->getLeagueLevel()->getLabel())
            ),
            "commentedAt"      =>$comment->getCommentedAt()
        );

        $responseDatas = array(
            'publishResponse' => 1,
            'commentPublished' => $commentPublished,
            'comment' => $comment,
        ); 
        
        return $responseDatas;
    }
    
    public function onError($errors)
    {
        $responseDatas = array(
            'publishResponse'   => -1,
            'errorMessage'      => "Le formulaire n'est pas valide.",
            'errors'            => $errors
        ); 
        
        return $responseDatas;
    }
    
    public function onIsNotXmlHttpRequest()
    {
        $responseDatas = array(
            'publishResponse'   => -1,
            'errorMessage'      => "La requête n'est pas une requête ajax."
        ); 
        
        return $responseDatas;
    }
}
