<?php

declare(strict_types=1);


namespace Covid19\Vaccine;


class Appointment
{
    private $appointmentId;
    private $begin;
    private $centerDescription;
    private $city;
    private $day;
    private $direction;
    private $vacuna;

    public function __construct(
        string $appointmentId,
        string $begin,
        string $centerDescription,
        string $city,
        string $day,
        string $direction,
        string $vacuna
    )
    {
        $this->appointmentId = $appointmentId;
        $this->begin = $begin;
        $this->centerDescription = $centerDescription;
        $this->city = $city;
        $this->day = $day;
        $this->direction = $direction;
        $this->vacuna = $vacuna;
    }

    public function getAppointmentId(): string
    {
        return $this->appointmentId;
    }

    public function getBegin(): string
    {
        return $this->begin;
    }

    public function getCenterDescription(): string
    {
        return $this->centerDescription;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getDay(): string
    {
        return $this->day;
    }

    public function getDirection(): string
    {
        return $this->direction;
    }

    public function getVacuna(): string
    {
        return $this->vacuna;
    }

    public function getDayFormatted(): string
    {
        return \DateTime::createFromFormat("dmY", $this->day)->format("D, d-m-Y");
    }
}