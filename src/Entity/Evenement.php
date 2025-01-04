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
use App\Repository\EvenementRepository;
use App\State\EvenementProcessor;
use App\State\EvenementProvider;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(provider: EvenementProvider::class),
        new Delete(security: "is_granted('PERM_EDIT', object)"),
        new Post(
            denormalizationContext: ["groups" => ["evenement:create"]],
            security: "is_granted('ROLE_ORGANISATEUR')",
            validationContext: ["groups" => ["Default", "evenement:create"]],
            processor: EvenementProcessor::class
        ),
        new Patch(
            denormalizationContext: ["groups" => ["evenement:update"]],
            security: "object.getOrganisateur() == user",
            validationContext: ["groups" => ["Default", "evenement:update"]]
        ),
        new GetCollection(
            uriTemplate: '/utilisateurs/{idUtilisateur}/evenementsOrganises',
            uriVariables: [
                'idUtilisateur' => new Link(
                    fromProperty: 'evenementsOrganises',
                    fromClass: Utilisateur::class
                )
            ],
        ),
    ],
    normalizationContext: ["groups" => ["evenement:read"]],
    order: ["dateDebut" => "ASC"],
)]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['evenement:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotNull(groups: ["evenement:create"])]
    #[Assert\NotBlank(groups: ["evenement:create"])]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Le nom est trop court! (3 caractères minimum)",
        maxMessage: "Le nom est trop long! (255 caractères maximum)"
    )]
    #[Groups(['evenement:read', 'evenement:create'])]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotNull(groups: ["evenement:create"])]
    #[Assert\NotBlank(groups: ["evenement:create"])]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Le nom est trop court! (3 caractères minimum)",
        maxMessage: "Le nom est trop long! (255 caractères maximum)"
    )]
    #[Groups(['evenement:read', 'evenement:create'])]
    private ?string $adresse = null;

    #[ORM\Column]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'Pas de prix négatif !')]
    #[Groups(['evenement:read', 'evenement:create'])]
    private ?int $prix = null;

    #[ORM\Column(nullable: true)]
    #[Assert\GreaterThanOrEqual(value: 4, message: 'Pour organiser un tournois, il faut au moins 4 équipes !')]
    #[Groups(['evenement:read', 'evenement:create'])]
    private ?int $equipeMax = null;

    #[ORM\Column]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'Pas de cashPrize négatif !')]
    #[Groups(['evenement:read', 'evenement:create', 'evenement:update'])]
    private ?int $cashPrize = null;

    #[ORM\Column(options: ["default" => false])]
    #[Groups(['evenement:read', 'evenement:create', 'evenement:update'])]
    private ?bool $public = null;

    #[ORM\ManyToOne(fetch: 'EAGER', inversedBy: 'evenementsOrganises')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    #[ApiProperty(writable: false)]
    #[Groups(['evenement:read'])]
    private ?Utilisateur $organisateur = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(groups: ["evenement:create"])]
    #[Assert\GreaterThanOrEqual("today", message: "La date de début doit être dans le futur.", groups: ["evenement:create"])]
    #[Groups(['evenement:read', 'evenement:create'])]
    private ?DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(groups: ["evenement:create"])]
    #[Assert\GreaterThanOrEqual("today", message: "La date de fin doit être dans le futur.", groups: ["evenement:create"])]
    #[Assert\GreaterThan(propertyPath: 'dateDebut', message: "La date de fin doit être après la date de début.", groups: ["evenement:create"])]
    #[Groups(['evenement:read', 'evenement:create'])]
    private ?DateTimeInterface $dateFin = null;

    /**
     * @var Collection<int, Equipe>
     */
    #[ORM\ManyToMany(targetEntity: Equipe::class, mappedBy: 'evenements')]
    #[Groups(['public:read','evenement:read'])]
    private Collection $equipes;


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

    public function isComplet(): ?int
    {
        if (is_null($this->equipeMax)) {
            return false;
        }
        return $this->equipeMax <= count($this->equipes);
    }

    public function getEquipeMax(): ?int
    {
        return $this->equipeMax;
    }

    public function setEquipeMax(?int $equipeMax): void
    {
        $this->equipeMax = $equipeMax;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getPrix(): ?int
    {
        return $this->prix;
    }

    public function setPrix(int $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getCashPrize(): ?int
    {
        return $this->cashPrize;
    }

    public function setCashPrize(int $cashPrize): static
    {
        $this->cashPrize = $cashPrize;

        return $this;
    }

    public function isPublic(): ?bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): static
    {
        $this->public = $public;

        return $this;
    }

    public function getOrganisateur(): ?Utilisateur
    {
        return $this->organisateur;
    }

    public function setOrganisateur(?Utilisateur $organisateur): static
    {
        $this->organisateur = $organisateur;

        return $this;
    }

    public function getDateDebut(): ?DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;

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
            $equipe->addEvenement($this);
        }

        return $this;
    }

    public function removeEquipe(Equipe $equipe): static
    {
        if ($this->equipes->removeElement($equipe)) {
            $equipe->removeEvenement($this);
        }

        return $this;
    }

}
