<?php

namespace App\Contracts\Article;

interface HasAuthorName
{
    public function getAuthorName(): ?string;
}
