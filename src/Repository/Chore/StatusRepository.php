<?php

namespace App\Repository\Chore;

use App\Entity\Chore\Status;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Status>
 *
 * @method Status|null find($id, $lockMode = null, $lockVersion = null)
 * @method Status|null findOneBy(array $criteria, array $orderBy = null)
 * @method Status[]    findAll()
 * @method Status[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Status::class);
    }

    function findDefaultUserRole()
    {
        return $this->findOneBy(['type' => Status::ROLE_TYPE ,'label' => 'ROLE_USER']);
    }

    public function findAllControllers(): array
    {
        return $this->findBy(['type' => Status::CONTROLLER_TYPE]);
    }

    public function findAllRoles(): array
    {
        return $this->findBy(['type' => Status::ROLE_TYPE]);
    }

    public function findRolesBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): array
    {
        return $this->findBy(
            array_merge($criteria, ['type' => Status::ROLE_TYPE]),
            $orderBy,
            $limit,
            $offset
        );
    }
}
