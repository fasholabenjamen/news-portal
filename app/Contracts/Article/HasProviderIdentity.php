<?php

namespace App\Contracts\Article;

interface HasProviderIdentity
{
    public function getProviderID(): string;
}
