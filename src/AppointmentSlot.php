<?php

declare(strict_types=1);


namespace Covid19\Vaccine;


class AppointmentSlot
{
    private $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }
}