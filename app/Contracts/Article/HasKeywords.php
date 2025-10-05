<?php

namespace App\Contracts\Article;

interface HasKeywords
{
    public function getKeywords(): ?string;
}
