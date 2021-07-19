<?php

declare(strict_types=1);

namespace Covid19\Vaccine\Service;


use Covid19\Vaccine\Appointment;
use Covid19\Vaccine\CatSalutClient;
use Covid19\Vaccine\Center;
use Covid19\Vaccine\Exception\NoCenterFoundException;
use Covid19\Vaccine\User;

class AppointmentFinder
{
    /**
     * @var CatSalutClient
     */
    private $client;

    private $centerIds;

    public function __construct(CatSalutClient $client, array $centerIds = [])
    {
        $this->client = $client;
        $this->centerIds = $centerIds;
    }

    public function findAppointment(User $user): Appointment
    {
        try {
            $centers = $this->client->getCenters($user);
        } catch (NoCenterFoundException $exc) {
            throw new \Exception("No centers available at the moment.");
        } catch (\DomainException $exc) {
            throw new \DomainException(sprintf("Something went wrong: %s", $exc->getMessage()));
        }


        foreach ($centers as $center) {
            if (\in_array($center->getCenterId(), $this->centerIds)) {
                return $this->confirmAppointment($user, $center);
            }
        }

        $centersFormatted = array_map(function (Center $center) {
            return sprintf("\t%s (%d)\n", $center->getCity(), $center->getCenterId());
        }, $centers);

        throw new \Exception(sprintf(
            "No centers available at the moment matching your criteria:\n%s",
            implode("\n", $centersFormatted)
        ));
    }

    private function confirmAppointment(User $user, Center $center): Appointment
    {
        $slot = $this->client->selectCenter($user, $center);
        try {
            return $this->client->confirmDate($user, $slot);
        } catch (\Exception $exc) {
            throw new \DomainException(sprintf(
                "Can't get an appointment in center %s (%s) for user %s: %s",
                $center->getCity(),
                $center->getCenterId(),
                $user->getCip(),
                $exc->getMessage(),
            ));
        }
    }
}