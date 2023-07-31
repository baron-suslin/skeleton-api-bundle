<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Requestum\ApiBundle\Repository\ApiRepositoryTrait;
use Requestum\ApiBundle\Repository\FilterableRepositoryInterface;

abstract class AbstractApiRepository extends ServiceEntityRepository implements FilterableRepositoryInterface
{
    use ApiRepositoryTrait;
}