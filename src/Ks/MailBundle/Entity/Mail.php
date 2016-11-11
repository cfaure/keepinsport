<?php

namespace Ks\MailBundle\Entity;

class Mail extends \Swift_Message
{
    function _contruct($subject, $body, $contentType, $charset) {

        parent::__construct($subject, $body, $contentType, $charset);
        $this->setCharset('UFT-8');
        $this->setContentType('text/html');
    }

    public function generateId() {
        
    }
}