<?php

declare(strict_types=1);


namespace Covid19\Vaccine;


class User
{
    private $cip;
    private $queueToken;
    private $smsCode;
    private $authToken;

    public function __construct(string $cip, string $queueToken, string $authToken, string $smsCode)
    {
        $this->cip = $cip;
        $this->queueToken = $queueToken;
        $this->smsCode = $smsCode;
        $this->authToken = $authToken;
    }

    public function getCip(): string
    {
        return $this->cip;
    }

    public function getQueueToken(): string
    {
        return $this->queueToken;
    }

    public function getSmsCode(): string
    {
        return $this->smsCode;
    }

    public function getAuthToken(): string
    {
        return $this->authToken;
    }
}