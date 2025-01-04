<?php

namespace App\Repository;


use App\Entity\Club;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Club>
 */
interface ClubRepositoryInterface
{
    public function getCoachClub(UserInterface $utilisateur);
}