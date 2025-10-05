<?php

namespace App\Contracts\Article;

interface HasSource
{
    public function getSourceName(): string;

    public function getSourceKey() : string;
}
