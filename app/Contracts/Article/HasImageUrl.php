<?php

namespace App\Contracts\Article;

interface HasImageUrl
{
    public function getImageUrl(): ?string;
}
