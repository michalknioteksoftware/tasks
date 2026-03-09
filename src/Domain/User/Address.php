<?php

declare(strict_types=1);

namespace App\Domain\User;

final class Address
{
    public function __construct(
        private readonly ?string $street,
        private readonly ?string $suite,
        private readonly ?string $city,
        private readonly ?string $zipcode,
        private readonly ?string $geoLat,
        private readonly ?string $geoLng,
    ) {
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function getSuite(): ?string
    {
        return $this->suite;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    public function getGeoLat(): ?string
    {
        return $this->geoLat;
    }

    public function getGeoLng(): ?string
    {
        return $this->geoLng;
    }
}

