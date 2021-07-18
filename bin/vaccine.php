<?php

declare(strict_types=1);

use Covid19\Vaccine\Appointment;
use Covid19\Vaccine\CatSalutClient;
use Covid19\Vaccine\Request;
use Covid19\Vaccine\Service\AppointmentFinder;
use Covid19\Vaccine\User;
use React\EventLoop\Loop;
use React\EventLoop\TimerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

require_once __DIR__ . "/../vendor/autoload.php";

$usersData = \file_get_contents(__DIR__ . "/../config/users.json");
$centers = require_once __DIR__ . '/../config/centers.php';

$request = new Request(
    "https://frontdoornodepro.azurefd.net",
    "https://vacunacovid.catsalut.gencat.cat",
    "application/json",
);

$loop = Loop::get();
$serializer = new Serializer(
    [new GetSetMethodNormalizer(), new ArrayDenormalizer()],
    [new JsonEncoder()]
);
$client = new CatSalutClient($request, $serializer);
$appointFinder = new AppointmentFinder($client, $centers);

$usersFromFile = $serializer->deserialize($usersData, User::class . "[]", "json");
$users = new SplQueue();

foreach ($usersFromFile as $userFromFile) {
    $users->push($userFromFile);
}

$loop->addPeriodicTimer(15, function (TimerInterface $timer) use ($appointFinder, $loop, $users) {
    $unresolved = [];
    while (!$users->isEmpty()) {
        $user = $users->pop();
        try {
            $appointment = $appointFinder->findAppointment($user);
            echo formatSuccessMessage($appointment);
        } catch (Exception $exception) {
            $unresolved[] = $user;
            echo sprintf("\t%s\n", $exception->getMessage());
        }
    }

    if (empty($unresolved)) {
        $loop->cancelTimer($timer);
        echo "\tAppointments obtained successfully!\n";

        return;
    }

    foreach ($unresolved as $unresolvedUser) {
        $users->push($unresolvedUser);
    }
});

$loop->run();

function formatSuccessMessage(Appointment $appointment): string
{
    return sprintf("\tCongratulations!!! Here you have your appointment!
                * ID: %s
                * Time: %s
                * Day: %s
                * Center: %s
                * City: %s
                * Address: %s
                * Vaccine: %s
        As well, check your e-mail :)\n\n",
        $appointment->getAppointmentId(),
        $appointment->getBegin(),
        $appointment->getDayFormatted(),
        $appointment->getCenterDescription(),
        $appointment->getCity(),
        $appointment->getDirection(),
        $appointment->getVacuna(),
    );
}

