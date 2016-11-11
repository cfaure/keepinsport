<?php

namespace Ks\PaymentBundle\Service;

//use Symfony\Bundle\DoctrineBundle\Registry;
//use Ks\PaymentBundle\User;

/**
 * Description of PaymentBundle\Service\Provider
 *
 * @author CFA
 */
class Provider
{
    //protected $_doctrine;
    protected $site;
    protected $rang;
    protected $identifiant;
    protected $hmac;
    protected $server;
    protected $repondre_a;

    /**
     *
     * @param type $hmac
     * @param type $server
     */
    public function __construct($site, $rang, $identifiant, $hmac, $server, $repondre_a)
    {
        foreach (array('site', 'rang', 'identifiant', 'hmac', 'server', 'repondre_a') as $attribute) {
            $this->$attribute = $$attribute;
        }
    }

    /**
     *
     * @return type
     */
    protected function getHmac()
    {
        return $this->hmac;
    }

    /**
     * 
     * @param type $total
     * @param type $refCmd
     * @param type $holderEmail
     * @return type
     */
    protected function hash($msg)
    {
        // La clé est en ASCII, on la transforme en binaire
        $binKey = pack("H*", $this->hmac);
        
        // La chaîne sera envoyée en majuscules, d'où l'utilisation de strtoupper()
        $hashedMsg = strtoupper(hash_hmac('sha512', $msg, $binKey));
        
        return $hashedMsg;
    }
    
    /**
     * NOTE CF: possible de faire un paiement en 4x (1 à la commande + échéancier jusqu'à 3 paiements)
     * 
     * @param array $amounts
     * @param type $refCmd
     * @param type $holderEmail
     * @return string
     */
    public function buildForm(array $amounts, $refCmd, $holderEmail)
    {
        $dateTime         = date('c');
        $numPayments      = count($amounts);
        
        // Quelques vérifications
        if ($numPayments < 1 || $numPayments > 4) {
            throw new \Exception('Nombre de mensualités de paiement non supporté: '.$numPayments);
        }
        if ($amounts[0] <= 0) {
            throw new \Exception('Montant de paiement invalide: '.$amounts[0]);
        }
        
        $PBX_TOTAL        = $amounts[0] * 100.0; // conversion € => centimes d'€
        $PBX_DEVISE       = 978;
        $PBX_TYPEPAIEMENT = "CARTE";
        $PBX_TYPECARTE    = "CB";
        $PBX_CMD          = $refCmd;
        $PBX_PORTEUR      = $holderEmail;
        $PBX_RETOUR       = "ref:R;trans:T;idtrans:S;auto:A;tarif:M;erreur:E;sign:K";
        $PBX_HASH         = "SHA512";
        $PBX_TIME         = $dateTime;
        
        // On crée la chaîne à hacher sans URLencodage
        // ATTENTION : l'ordre des champs dans le formulaire devra
        // correspondre exactement à'ordre des champs dans la chaîne hachée
        $msg = "PBX_SITE=".$this->site
            ."&PBX_RANG=".$this->rang
            ."&PBX_IDENTIFIANT=".$this->identifiant
            ."&PBX_TOTAL=$PBX_TOTAL"
            ."&PBX_DEVISE=$PBX_DEVISE"
            ."&PBX_TYPEPAIEMENT=$PBX_TYPEPAIEMENT"
            ."&PBX_TYPECARTE=$PBX_TYPECARTE"
            ."&PBX_CMD=$PBX_CMD"
            ."&PBX_PORTEUR=$PBX_PORTEUR"
            ."&PBX_RETOUR=$PBX_RETOUR"
            ."&PBX_HASH=$PBX_HASH"
            ."&PBX_TIME=$PBX_TIME"
            ."&PBX_REPONDRE_A=".$this->repondre_a
        ;
        
        // gestion du paiement en n fois (pour le message)
        // NOTE CF: $amounts[0] correspondra toujours au 1er paiement
        
        // NOTE CF: TODO: faire un test curl sur la disponibilité du serveur
        $form = '
            <form method="post" action="https://'.$this->server.'/cgi/MYchoix_pagepaiement.cgi">
                <input type="hidden" name="PBX_SITE"        value="'.$this->site.'" />
                <input type="hidden" name="PBX_RANG"        value="'.$this->rang.'" />
                <input type="hidden" name="PBX_IDENTIFIANT" value="'.$this->identifiant.'" />
                <input type="hidden" name="PBX_TOTAL"       value="'.$PBX_TOTAL.'" />
                <input type="hidden" name="PBX_DEVISE"      value="'.$PBX_DEVISE.'" />
                <input type="hidden" name="PBX_TYPEPAIEMENT" value="'.$PBX_TYPEPAIEMENT.'" />
                <input type="hidden" name="PBX_TYPECARTE"   value="'.$PBX_TYPECARTE.'" />
                <input type="hidden" name="PBX_CMD"         value="'.$PBX_CMD.'" />
                <input type="hidden" name="PBX_PORTEUR"     value="'.$PBX_PORTEUR.'" />
                <input type="hidden" name="PBX_RETOUR"      value="'.$PBX_RETOUR.'" />
                <input type="hidden" name="PBX_HASH"        value="'.$PBX_HASH.'" />
                <input type="hidden" name="PBX_TIME"        value="'.$PBX_TIME.'" />'
              .'<input type="hidden" name="PBX_REPONDRE_A"  value="'.$this->repondre_a.'" />';
        
        // Gestion du paiement en nfois (pour le formulaire)
        for ($i = 1; $i < $numPayments; ++$i) {
            $curAmount = $amounts[$i] * 100.0; // conversion € => centimes d'€
            $PBX_DATE  = date('d/m/Y', mktime(0, 0, 0,date('m') + $i, date('d'), date('Y'))); // on part de la date du jour et on étale les paiements les n mois suivants
            $msg      .= "&PBX_2MONT$i=$curAmount"
                . "&PBX_DATE$i=$PBX_DATE";
            $form     .= '<input type="hidden" name="PBX_2MONT'.$i.'" value="'.$curAmount.'" />'
                .'<input type="hidden" name="PBX_DATE'.$i.'"  value="'.$PBX_DATE.'" />';
        }
        
        // Hash du message et finalisation du formulaire
        $hash  = $this->hash($msg);
        $form .= '<input type="hidden" name="PBX_HMAC"      value="'.$hash.'" />'
            .'</form>';
        
        return $form;
    }
}