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
use App\Repository\UtilisateurRepository;
use App\State\UtilisateurProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_LOGIN', fields: ['login'])]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['adresseEmail'])]
#[UniqueEntity('login', message: 'Le login existe déjà')]
#[UniqueEntity('adresseEmail', message: 'adresse mail non unique')]
#[ApiResource(
    operations: [
        new Get(),
        new Delete(security: "is_granted('PERM_DELETE', object)"),
        new GetCollection(),
        new GetCollection(
            uriTemplate: '/equipes/{idEquipe}/joueurs',
            uriVariables: [
                'idEquipe' => new Link(
                    fromProperty: 'joueurs',
                    fromClass: Equipe::class
                )
            ],
        ),
        new Post(
            denormalizationContext: ["groups" => ["utilisateur:create"]],
            validationContext: ["groups" => ["Default", "utilisateur:create"]],
            processor: UtilisateurProcessor::class),
        new Patch(
            denormalizationContext: ["groups" => ["utilisateur:update"]],
            security: "is_granted('PERM_DELETE', object)",
            validationContext: ["groups" => ["Default", "utilisateur:update"]],
            processor: UtilisateurProcessor::class, ),
    ],
    normalizationContext: ["groups" => ["utilisateur:read"]])]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['utilisateur:read', "club:read", 'equipe:read','evenement:read','club:details'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(groups: ["utilisateur:create"])]
    #[Assert\NotNull(groups: ["utilisateur:create"])]
    #[Groups(['utilisateur:read', 'club:read', 'utilisateur:create', 'equipe:read'])]
    #[Assert\Length(min:4, max: 20,  minMessage: 'Il faut au minimum 4 caractères', maxMessage: 'Il faut au maximum 200 caractères')]
    private ?string $login = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Groups(['utilisateur:read','utilisateur:update'])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[ApiProperty(readable: false, writable: false)]
    private ?string $password = null;


    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(groups: ["utilisateur:create"])]
    #[Assert\NotNull(groups: ["utilisateur:create"])]
    #[Assert\Email(message: 'Il faut une adresse mail valide')]
    #[Groups(['utilisateur:read', "club:read", 'utilisateur:create', 'utilisateur:update', "evenement:read",'club:details'])]
    private ?string $adresseEmail = null;


    #[Assert\NotBlank(groups: ["utilisateur:create"])]
    #[Assert\NotNull(groups: ["utilisateur:create"])]
    #[Assert\Length(min: 8, max:30, minMessage: 'Il faut 8 caractères minimum', maxMessage:'Il faut au maximum 30 caractères')]
    #[Assert\Regex("#^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d\w\W]{8,30}$#", message: 'l\'expression n\'est pas valide' )]
    #[Groups(['utilisateur:create', 'utilisateur:update'])]
    #[ApiProperty(readable: false)]
    private ?string $plainPassword = null;

    #[Assert\NotBlank(groups: ["utilisateur:update"])]
    #[Assert\NotNull(groups: ["utilisateur:update"])]
    #[Assert\Regex("#^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d\w\W]{8,30}$#", message: 'l\'expression n\'est pas valide' )]
    #[Assert\Length(min: 8, max:30, minMessage: 'Il faut 8 caractères minimum', maxMessage:'Il faut au maximum 30 caractères')]
    #[Groups(['utilisateur:update'])]
    #[ApiProperty(readable: false)]
    #[UserPassword(groups : ["utilisateur:update"])]
    private ?string $currentPlainPassword = null;

    /**
     * @var Collection<int, Evenement>
     */
    #[ORM\OneToMany(targetEntity: Evenement::class, mappedBy: 'organisateur', orphanRemoval: true)]
    private Collection $evenementsOrganises;

    #[ORM\ManyToOne(inversedBy: 'joueurs')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['utilisateur:read', 'utilisateur:update'])]
    private ?Equipe $equipe = null;

    #[ORM\OneToOne(mappedBy: 'coach', cascade: ['persist', 'remove'])]
    #[Groups(['utilisateur:read'])]
    private ?Club $club = null;

    #[ORM\Column(length: 32, nullable: true)]
    #[Groups(['utilisateur:read', 'utilisateur:update'])]
    private ?string $codeAnnuaire = null;

    public function __construct()
    {
        $this->evenementsOrganises = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }
    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): static
    {
        $this->login = $login;

        return $this;
    }

    public function getCurrentPlainPassword(): ?string
    {
        return $this->currentPlainPassword;
    }

    public function setCurrentPlainPassword(?string $currentPlainPassword): void
    {
        $this->currentPlainPassword = $currentPlainPassword;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->login;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
        $this->currentPlainPassword = null;
    }

    public function getAdresseEmail(): ?string
    {
        return $this->adresseEmail;
    }

    public function setAdresseEmail(string $adresseEmail): static
    {
        $this->adresseEmail = $adresseEmail;

        return $this;
    }

    /**
     * @return Collection<int, Evenement>
     */
    public function getEvenementsOrganises(): Collection
    {
        return $this->evenementsOrganises;
    }

    public function addEvenementsOrganis(Evenement $evenementsOrganis): static
    {
        if (!$this->evenementsOrganises->contains($evenementsOrganis)) {
            $this->evenementsOrganises->add($evenementsOrganis);
            $evenementsOrganis->setOrganisateur($this);
        }

        return $this;
    }

    public function removeEvenementsOrganis(Evenement $evenementsOrganis): static
    {
        if ($this->evenementsOrganises->removeElement($evenementsOrganis)) {
            // set the owning side to null (unless already changed)
            if ($evenementsOrganis->getOrganisateur() === $this) {
                $evenementsOrganis->setOrganisateur(null);
            }
        }

        return $this;
    }

    public function getEquipe(): ?Equipe
    {
        return $this->equipe;
    }

    public function setEquipe(?Equipe $equipe): static
    {
        $this->equipe = $equipe;

        return $this;
    }

    public function getClub(): ?Club
    {
        return $this->club;
    }

    public function setClub(?Club $club): static
    {
        // unset the owning side of the relation if necessary
        if ($club === null && $this->club !== null) {
            $this->club->setCoach(null);
        }

        // set the owning side of the relation if necessary
        if ($club !== null && $club->getCoach() !== $this) {
            $club->setCoach($this);
        }

        $this->club = $club;

        return $this;
    }

    public function getCodeAnnuaire(): ?string
    {
        return $this->codeAnnuaire;
    }

    public function setCodeAnnuaire(?string $codeAnnuaire): static
    {
        $this->codeAnnuaire = $codeAnnuaire;

        return $this;
    }

}
