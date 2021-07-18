<?php

declare(strict_types=1);


namespace Covid19\Vaccine;


class Request
{
    private $origin;
    private $contentType;
    private $baseUri;

    public function __construct(
        string $baseUri,
        string $origin,
        string $contentType
    )
    {
        $this->baseUri = $baseUri;
        $this->origin = $origin;
        $this->contentType = $contentType;
    }

    public function getOrigin(): string
    {
        return $this->origin;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function getBaseUri()
    {
        return $this->baseUri;
    }
}