<?php

namespace App\Contracts\Article;

interface HasCategory
{
    public function getCategory(): ?string;
}
