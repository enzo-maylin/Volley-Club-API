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
use App\Repository\EquipeRepository;
use App\State\EquipeProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EquipeRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new GetCollection(
            uriTemplate: '/clubs/{idClub}/equipes',
            uriVariables: [
                'idClub' => new Link(
                    fromProperty: 'equipes',
                    fromClass: Club::class
                )
            ],
        ),
        new Post(
            denormalizationContext: ["groups" => ["equipe:create"]],
            security: "is_granted('ROLE_COACH')",
            validationContext: ["groups" => ["Default", "equipe:create"]],
            processor: EquipeProcessor::class,
        ),
        new Patch(
            denormalizationContext: ["equipe:update"],
            security: "is_granted('ROLE_COACH') and object.getClub().getCoach() == user",
            validationContext: ["groups" => ["Default", "equipe:update"]],
        ),
        new Delete(security: "is_granted('ROLE_COACH') and object.getClub().getCoach() == user"),
    ], normalizationContext: ["groups" => ["equipe:read"]]
)]
class Equipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['equipe:read','club:details','utilisateur:read','evenement:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotNull(groups: ["equipe:create"])]
    #[Assert\NotBlank(groups: ["equipe:create"])]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Le nom est trop court! (3 caractères minimum)",
        maxMessage: "Le nom est trop long! (255 caractères maximum)"
    )]
    #[Groups(['equipe:read', "club:read", "equipe:create", "equipe:update",'club:details','utilisateur:read','evenement:read'])]
    private ?string $nom = null;

    /**
     * @var Collection<int, Utilisateur>
     */
    #[ORM\OneToMany(targetEntity: Utilisateur::class, mappedBy: 'equipe')]
    #[Groups(['equipe:read', "club:read",'club:details'])]
    private Collection $joueurs;

    /**
     * @var Collection<int, Evenement>
     */
    #[ORM\ManyToMany(targetEntity: Evenement::class, inversedBy: 'equipes')]
    #[Groups(['equipe:read'])]
    private Collection $evenements;

    #[ORM\ManyToOne(fetch: 'EAGER', inversedBy: 'equipes')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['equipe:read','utilisateur:read','evenement:read'])]
    #[ApiProperty(writable: false)]
    private ?Club $club = null;

    #[ORM\PreRemove]
    public function onPreRemove(): void
    {
        foreach ($this->joueurs as $joueur) {
            $joueur->setEquipe(null); // Met l'équipe à null pour chaque joueur
        }
    }

    public function __construct()
    {
        $this->joueurs = new ArrayCollection();
        $this->evenements = new ArrayCollection();
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
     * @return Collection<int, Utilisateur>
     */
    public function getJoueurs(): Collection
    {
        return $this->joueurs;
    }

    public function addJoueur(Utilisateur $joueur): static
    {
        if (!$this->joueurs->contains($joueur)) {
            $this->joueurs->add($joueur);
            $joueur->setEquipe($this);
        }

        return $this;
    }

    public function removeJoueur(Utilisateur $joueur): static
    {
        if ($this->joueurs->removeElement($joueur)) {
            // set the owning side to null (unless already changed)
            if ($joueur->getEquipe() === $this) {
                $joueur->setEquipe(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Evenement>
     */
    public function getEvenements(): Collection
    {
        return $this->evenements;
    }

    public function addEvenement(Evenement $evenement): static
    {
        if (!$this->evenements->contains($evenement)) {
            $this->evenements->add($evenement);
        }

        return $this;
    }

    public function removeEvenement(Evenement $evenement): static
    {
        $this->evenements->removeElement($evenement);

        return $this;
    }

    public function getClub(): ?Club
    {
        return $this->club;
    }

    public function setClub(?Club $club): static
    {
        $this->club = $club;

        return $this;
    }
}
