<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Evenement;

class EvenementProvider implements ProviderInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []):  object|array|null
    {
        $today = new \DateTime();

        $query = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from(Evenement::class, 'e')
            ->where('e.dateDebut >= :today')
            ->setParameter('today', $today)
            ->orderBy('e.dateDebut', 'ASC')
            ->getQuery();

        return $query->getResult();
    }
}
