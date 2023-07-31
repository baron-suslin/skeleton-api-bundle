<?php

namespace App\Tests\Action;

class ParamWrapper
{
    public function __construct(
        private string $class,
        private array $criteria,
        private string $path = 'id'
    ) {}

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setClass(string $class): void
    {
        $this->class = $class;
    }

    public function getCriteria(): array
    {
        return $this->criteria;
    }

    public function setCriteria(array $criteria): void
    {
        $this->criteria = $criteria;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }
}
