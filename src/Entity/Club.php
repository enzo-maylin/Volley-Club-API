<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\ClubRepository;
use App\State\ClubProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClubRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new Get(
            uriTemplate: '/utilisateur/{idUtilisateur}/club',
            uriVariables: [
                'idUtilisateur' => new Link(
                    fromProperty: 'club',
                    fromClass: Utilisateur::class
                )
            ],
            normalizationContext: ['groups' => ['club:details']]
        ),
        new GetCollection(),
        new Post(
            denormalizationContext: ["groups" => ["club:create"]],
            security: "is_granted('ROLE_COACH')",
            validationContext: ["groups" => ["Default", "club:create"]],
            processor: ClubProcessor::class,
        ),
        new Delete(
            security: "is_granted('ROLE_COACH') and object.getCoach() == user",
        ),
    ],
    normalizationContext: ["groups" => ["club:read"]],
)]
class Club
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["club:read", 'equipe:read','utilisateur:read','club:details','evenement:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotNull(groups: ["club:create"])]
    #[Assert\NotBlank(groups: ["club:create"])]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Le nom est trop court! (3 caractères minimum)",
        maxMessage: "Le nom est trop long! (255 caractères maximum)"
    )]
    #[Groups(['club:read', 'club:create', 'equipe:read','utilisateur:read','club:details'])]
    private ?string $nom = null;

    /**
     * @var Collection<int, Equipe>
     */
    #[ORM\OneToMany(targetEntity: Equipe::class, mappedBy: 'club', orphanRemoval: true)]
    #[Groups(["club:read",'club:details'])]
    private Collection $equipes;

    #[ORM\OneToOne(inversedBy: 'club', cascade: ['persist'])]
    #[Groups(["club:read", "equipe:read"])]
    #[ApiProperty(description: 'Le coach du club', writable: false)]
    private ?Utilisateur $coach = null;


    public function __construct()
    {
        $this->equipes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection<int, Equipe>
     */
    public function getEquipes(): Collection
    {
        return $this->equipes;
    }

    public function addEquipe(Equipe $equipe): static
    {
        if (!$this->equipes->contains($equipe)) {
            $this->equipes->add($equipe);
            $equipe->setClub($this);
        }

        return $this;
    }

    public function removeEquipe(Equipe $equipe): static
    {
        if ($this->equipes->removeElement($equipe)) {
            // set the owning side to null (unless already changed)
            if ($equipe->getClub() === $this) {
                $equipe->setClub(null);
            }
        }

        return $this;
    }

    public function getCoach(): ?Utilisateur
    {
        return $this->coach;
    }

    public function setCoach(?Utilisateur $coach): static
    {
        $this->coach = $coach;

        return $this;
    }

}
