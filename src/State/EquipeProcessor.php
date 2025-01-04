<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Equipe;
use App\Repository\ClubRepositoryInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class EquipeProcessor implements ProcessorInterface
{

    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private ClubRepositoryInterface $clubRepository,
        private Security $security,
    )
    {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        /** @var Equipe $data */
        $data->setClub($this->clubRepository->getCoachClub($this->security->getUser()));
        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
