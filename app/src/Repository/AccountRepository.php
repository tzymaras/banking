<?php
namespace App\Repository;

use App\Entity\Account;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Account|null find($id, $lockMode = null, $lockVersion = null)
 * @method Account|null findOneBy(array $criteria, array $orderBy = null)
 * @method Account[]    findAll()
 * @method Account[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Account::class);
    }

    /**
     * @param $iban
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @return Account|null
     */
    public function findOneByIBAN($iban): ?Account
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.iban = :iban')
            ->setParameter('iban', $iban)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
