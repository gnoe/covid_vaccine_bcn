<?php

declare(strict_types=1);

namespace Covid19\Vaccine;

class Center
{
    const MATARO = 25;
    const BARBERA_DEL_VALLES = 30;
    const HOSPITALET_DE_LLOBREGAT = 60;
    const BADALONA = 102;
    const BARCELONA_1 = 120;
    const BARCELONA_2 = 126;
    const BARCELONA_3 = 1082;


    private $city;

    private $centerId;

    private $availableDays;

    public function __construct(string $city, int $centerId, array $availableDays)
    {
        $this->city = $city;
        $this->centerId = $centerId;
        $this->availableDays = $availableDays;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getCenterId(): int
    {
        return $this->centerId;
    }

    public function getAvailableDays(): array
    {
        return $this->availableDays;
    }

    public function getFirstDate(): string
    {
        \reset($this->availableDays);

        return \current($this->availableDays);
    }
}