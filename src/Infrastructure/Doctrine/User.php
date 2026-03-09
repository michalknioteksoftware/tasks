<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $username = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $addressStreet = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $addressSuite = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $addressCity = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $addressZipcode = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $addressGeoLat = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $addressGeoLng = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $website = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyCatchPhrase = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyBs = null;

    #[ORM\Column(name: 'is_admin', options: ['default' => false])]
    private bool $isAdmin = false;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getAddressStreet(): ?string
    {
        return $this->addressStreet;
    }

    public function setAddressStreet(?string $addressStreet): static
    {
        $this->addressStreet = $addressStreet;

        return $this;
    }

    public function getAddressSuite(): ?string
    {
        return $this->addressSuite;
    }

    public function setAddressSuite(?string $addressSuite): static
    {
        $this->addressSuite = $addressSuite;

        return $this;
    }

    public function getAddressCity(): ?string
    {
        return $this->addressCity;
    }

    public function setAddressCity(?string $addressCity): static
    {
        $this->addressCity = $addressCity;

        return $this;
    }

    public function getAddressZipcode(): ?string
    {
        return $this->addressZipcode;
    }

    public function setAddressZipcode(?string $addressZipcode): static
    {
        $this->addressZipcode = $addressZipcode;

        return $this;
    }

    public function getAddressGeoLat(): ?string
    {
        return $this->addressGeoLat;
    }

    public function setAddressGeoLat(?string $addressGeoLat): static
    {
        $this->addressGeoLat = $addressGeoLat;

        return $this;
    }

    public function getAddressGeoLng(): ?string
    {
        return $this->addressGeoLng;
    }

    public function setAddressGeoLng(?string $addressGeoLng): static
    {
        $this->addressGeoLng = $addressGeoLng;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): static
    {
        $this->website = $website;

        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(?string $companyName): static
    {
        $this->companyName = $companyName;

        return $this;
    }

    public function getCompanyCatchPhrase(): ?string
    {
        return $this->companyCatchPhrase;
    }

    public function setCompanyCatchPhrase(?string $companyCatchPhrase): static
    {
        $this->companyCatchPhrase = $companyCatchPhrase;

        return $this;
    }

    public function getCompanyBs(): ?string
    {
        return $this->companyBs;
    }

    public function setCompanyBs(?string $companyBs): static
    {
        $this->companyBs = $companyBs;

        return $this;
    }

    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }

    public function setIsAdmin(bool $isAdmin): static
    {
        $this->isAdmin = $isAdmin;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}

