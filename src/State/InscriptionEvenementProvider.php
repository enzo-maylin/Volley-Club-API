<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\InscriptionEvenement;
use App\Repository\EquipeRepository;
use App\Repository\EvenementRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InscriptionEvenementProvider implements ProviderInterface
{
    public function __construct(
        private EquipeRepository $equipeRepository,
        private EvenementRepository $evenementRepository
    )
    {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        //$uriVariables contient les valeurs des variables fournies au travers de l'URI de la route
        $idEquipe = $uriVariables["idEquipe"];
        $equipe = $this->equipeRepository->find($idEquipe);
        if(!$equipe) {
            throw new NotFoundHttpException("Équipe inexistante.");
        }
        $idEvenement = $uriVariables["idEvenement"];
        $evenement = $this->evenementRepository->find($idEvenement);
        if(!$evenement) {
            throw new NotFoundHttpException("Évènement inexistant.");
        }

        if(!$equipe->getEvenements()->contains($evenement)) {
            return null;
        }

        $inscriptionEvenement = new InscriptionEvenement();
        $inscriptionEvenement->setEquipe($equipe);
        $inscriptionEvenement->setEvenement($evenement);
        return $inscriptionEvenement;
    }
}
