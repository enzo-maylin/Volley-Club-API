<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class InscriptionEvenementDeleteProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    )
    {}

    //$data est un objet Inscription fourni par le StateProvider.
    //Dans ce contexte (DELETE), il ne peut pas être null, sinon une exception NotFoundHttpException aurait été levée avant d'arriver ici.
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        $data->getEquipe()->removeEvenement($data->getEvenement());
        $this->entityManager->flush();
    }
}
