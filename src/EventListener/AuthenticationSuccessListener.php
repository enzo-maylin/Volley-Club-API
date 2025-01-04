<?php

namespace App\EventListener;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;

class AuthenticationSuccessListener
{
    public function __construct(
        //Service permettant de décoder un JWT (entre autres)
        private JWTTokenManagerInterface $jwtManager
    )
    {}

    /**
     * @param AuthenticationSuccessEvent $event
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();

        //Insertion de données de l'utilisateur ici - À compléter
        $data['id'] = $user->getId();
        $data['login'] = $user->getLogin();
        $data['adresseEmail'] = $user->getAdresseEmail();
        $data['roles'] = $user->getRoles();
        if(!is_null($user->getEquipe()))$data['id_equipe'] = $user->getEquipe()->getId();
        else $data['id_equipe']=null;
        $data['codeAnnuaire'] = $user->getCodeAnnuaire();

        //On décode le jwt qui est déjà encodé, à ce stade, afin de récupérer les informations qui nous intéressent.
        $jwt = $this->jwtManager->parse($data['token']);
        $data['token_exp'] = $jwt['exp'];

        $event->setData($data);
    }
}