<?php

namespace App\Helpers;

class ClientResponse
{
    public function __construct(public int $status_code, public ?string $error_msg = null, public array $data = [])
    {
    }

    public function isSuccessful(): bool
    {
        return $this->status_code >= 200 && $this->status_code < 300;
    }

    public function failed(): bool
    {
        return !$this->isSuccessful();
    }

    public function getErrorMessage(): ?string
    {
        return $this->error_msg;
    }

    public function setErrorMessage(?string $message): void
    {
        $this->error_msg = $message;
    }
}
