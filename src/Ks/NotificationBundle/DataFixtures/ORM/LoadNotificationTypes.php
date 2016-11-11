<?php

namespace Ks\NotificationBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use  Ks\NotificationBundle\Entity\NotificationType;

class LoadNotificationTypes implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        //Notifications liées aux demandes d'ami
        $notificationType_friendRequest = new NotificationType();
        $notificationType_friendRequest->setName('friend_request');
        $manager->persist($notificationType_friendRequest);
        
        //La notification de demande d'ami nécessitant une réponse
        $notificationType_askFriendRequest = new NotificationType();
        $notificationType_askFriendRequest->setName('ask_friend_request');
        $manager->persist($notificationType_askFriendRequest);
        
        //La notification pour un nouveau message
        $notificationType_message = new NotificationType();
        $notificationType_message->setName('message');
        $manager->persist($notificationType_message);
        
        //La notification pour un commentaire
        $notificationType_comment = new NotificationType();
        $notificationType_comment->setName('comment');
        $manager->persist($notificationType_comment);
        
        //La notification pour un vote
        $notificationType_vote = new NotificationType();
        $notificationType_vote->setName('vote');
        $manager->persist($notificationType_vote);
        
        //La notification pour un partage
        $notificationType_share = new NotificationType();
        $notificationType_share->setName('share');
        $manager->persist($notificationType_share);
        
        //La notification pour un changement de ligue
        $notificationType_league = new NotificationType();
        $notificationType_league->setName('league');
        $manager->persist($notificationType_league);
        
        //La notification pour une validation de session sportive
        $notificationType_ValidateActivity = new NotificationType();
        $notificationType_ValidateActivity->setName('validation_activity');
        $manager->persist($notificationType_ValidateActivity);

        
        //La notification pour une validation de session sportive
        $notificationType_ValidateActivity = new NotificationType();
        $notificationType_ValidateActivity->setName('invitation_event');
        $manager->persist($notificationType_ValidateActivity);

        
        //La notification pour la modification d'une activité
        $notificationType_edit = new NotificationType();
        $notificationType_edit->setName('edit');
        $manager->persist($notificationType_edit);
        
        //La notification pour le gain d'un badge
        $notificationType_trophy = new NotificationType();
        $notificationType_trophy->setName('trophy');
        $manager->persist($notificationType_trophy);
        
        //La notification pour validation activté partagé type teamsport
        $notificationType_activityMustBeValidated = new NotificationType();
        $notificationType_activityMustBeValidated->setName('mustBeValidated');
        $manager->persist($notificationType_activityMustBeValidated);
        
        $notificationType_activityMustBeValidatedEvent = new NotificationType();
        $notificationType_activityMustBeValidatedEvent->setName('mustBeValidatedEvent');
        $manager->persist($notificationType_activityMustBeValidatedEvent);
        
        $notificationType_teamComposition = new NotificationType();
        $notificationType_teamComposition->setName('teamComposition');
        $manager->persist($notificationType_teamComposition);
        
        $notificationType_warning = new NotificationType();
        $notificationType_warning->setName('warning');
        $manager->persist($notificationType_warning);
        
        $notificationType_eventParticipation = new NotificationType();
        $notificationType_eventParticipation->setName('eventParticipation');
        $manager->persist($notificationType_eventParticipation);   
        
        $notificationType_club = new NotificationType();
        $notificationType_club->setName('club');
        $manager->persist($notificationType_club);

        $manager->flush();
    }
}



