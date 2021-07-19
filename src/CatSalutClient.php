<?php

declare(strict_types=1);

namespace Covid19\Vaccine;

use Covid19\Vaccine\Exception\ConfirmDateException;
use Covid19\Vaccine\Exception\NoCenterFoundException;
use Covid19\Vaccine\Exception\SelectCenterException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CatSalutClient
{
    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(Request $request, SerializerInterface $serializer)
    {
        $this->client = HttpClient::create([
            'base_uri' => $request->getBaseUri(),
            'headers' => [
                "authority" => $request->getBaseUri(),
                "sec-ch-ua" => '" Not;A Brand";v="99", "Google Chrome";v="91", "Chromium";v="91"',
                "user-agent" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.114 Safari/537.36",
                "sec-fetch-site" => "cross-site",
                "sec-fetch-mode" => "cors",
                "sec-fetch-dest" => "empty",
                "Content-Type" => $request->getContentType(),
                "origin" => $request->getOrigin(),
                "referer" => $request->getOrigin(),
                "if-none-match" => 'W/"ed-P8wYidv0RP/J/vlaMcGCbST82oY"',
            ]
        ]);

        $this->serializer = $serializer;
    }


    /**
     * @return Center[]
     */
    public function getCenters(User $user): array
    {
        $response = $this->client->request("GET", "/sf/centers", $this->appendUserHeaders($user));

        $this->checkServerError($response);

        if ($response->getStatusCode() !== 200) return [];

        $decoded = $this->decodeResponse($response);

        if ($this->hasError($decoded)) {
            throw new NoCenterFoundException();
        }

        return $this->serializer->denormalize($decoded, Center::class . "[]");
    }

    public function selectCenter(User $user, Center $center): AppointmentSlot
    {
        $response = $this->client->request("POST", "sf/slots", $this->appendUserHeaders($user, [
            "json" => [
                "centerId" => $center->getCenterId(),
                "day" => $center->getFirstDate(),
            ],
        ]));

        $this->checkServerError($response);

        if ($response->getStatusCode() !== 200) throw new SelectCenterException($response->getContent(false));

        $decoded = $this->decodeResponse($response);

        if ($this->hasError($decoded)) {
            throw new SelectCenterException();
        }

        $slots = $this->serializer->denormalize($decoded, AppointmentSlot::class . "[]");

        return \current($slots);
    }

    public function confirmDate(User $user, AppointmentSlot $slot): Appointment
    {
        $response = $this->client->request("POST", "sf/schedule", $this->appendUserHeaders($user, [
            "json" => [
                "id" => $slot->getId(),
            ],
        ]));

        $this->checkServerError($response);

        if ($response->getStatusCode() !== 200) throw new ConfirmDateException($response->getContent(false));

        $decoded = $this->decodeResponse($response);

        if ($this->hasError($decoded)) {
            throw new ConfirmDateException();
        }

        /** @var Appointment $appointment */
        $appointment = $this->serializer->denormalize($decoded, Appointment::class);

        return $appointment;
    }

    private function decodeResponse(ResponseInterface $response): array
    {
        $decoded = json_decode($response->getContent(false), true);

        if (null === $decoded) return [];

        if (json_last_error() !== JSON_ERROR_NONE) throw new \Exception(sprintf("Couldn't decode response from %s: %s", $response->getInfo("url"), json_last_error_msg()));

        return $decoded;
    }

    private function appendUserHeaders(User $user, array $options = []): array
    {
        $headers = [
            "x-token" => $user->getSmsCode(),
            "x-queue-token" => $user->getQueueToken(),
            "x-auth-token" => $user->getAuthToken(),
            "cip" => $user->getCip(),
        ];

        if (\array_key_exists("headers", $options)) {
            $options["headers"] = \array_merge($headers, $options['headers']);
        } else {
            $options["headers"] = $headers;
        }

        return $options;
    }

    private function hasError(array $decoded): bool
    {
        return \array_key_exists("error", $decoded) || empty($decoded);
    }

    /**
     * @param ResponseInterface $response
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    private function checkServerError(ResponseInterface $response): void
    {
        if ($response->getStatusCode() === 500) {
            throw new \DomainException(sprintf("Request to %s failed: %s",
                $response->getInfo("url"),
                $response->getContent(false),
            ));
        }
    }
}