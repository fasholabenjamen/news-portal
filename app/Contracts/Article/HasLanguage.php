<?php

namespace App\Contracts\Article;

interface HasLanguage
{
    public function getLanguage(): ?string;
}
