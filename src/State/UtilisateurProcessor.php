<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UtilisateurProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        //Injection du service UserPasswordHasherInterface
        private UserPasswordHasherInterface $passwordHasher,
    ){}
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if($data->getPlainPassword() != null){
            $hashed = $this->passwordHasher->hashPassword($data, $data->getPlainPassword());
            //On met à jour l'attribut "password" de l'utilisateur
            $data->setPassword($hashed);
            $data->eraseCredentials();
        }
        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
