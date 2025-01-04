<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Put;
use App\State\InscriptionEvenementDeleteProcessor;
use App\State\InscriptionEvenementProvider;
use App\State\InscriptionEvenementPutProcessor;

#[ApiResource(
    uriTemplate: '/equipes/{idEquipe}/evenements/{idEvenement}',
    operations: [
        new Put(
            description: "Inscrit une équipe à un évènement",
            security: "is_granted('ROLE_COACH')",
            deserialize: false,
            processor: InscriptionEvenementPutProcessor::class,
            allowCreate: true,
        ),
        new Delete(
            description: "Retire une équipe d'un évènement",
            security: "is_granted('ROLE_COACH') and object.getEquipe().getClub().getCoach() == user",
            processor: InscriptionEvenementDeleteProcessor::class
        ),
        new Get(
            description: "Permet de vérifier si une équipe est inscrit à un évènement"
        ),
    ],
    uriVariables: [
        'idEquipe' => new Link(
            fromClass: Equipe::class
        ),
        'idEvenement' => new Link(
            fromClass: Evenement::class
        ),
    ],
    provider: InscriptionEvenementProvider::class
)]
class InscriptionEvenement
{
    #[ApiProperty(writable: false)]
    private ?Equipe $equipe = null;

    #[ApiProperty(writable: false)]
    private ?Evenement $evenement = null;

    public function getEquipe(): ?Equipe
    {
        return $this->equipe;
    }

    public function getEvenement(): ?Evenement
    {
        return $this->evenement;
    }

    public function setEquipe(?Equipe $equipe): self
    {
        $this->equipe = $equipe;
        return $this;
    }

    public function setEvenement(?Evenement $evenement): self
    {
        $this->evenement = $evenement;
        return $this;
    }
}