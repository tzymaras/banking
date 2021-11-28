<?php

namespace App\Repository;

use App\Entity\Account;
use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * @param Account $account
     * @return int|mixed[]|string
     */
    public function findAccountTransactions(Account $account)
    {
        return $this->createQueryBuilder('t')
            ->where('t.ibanFrom = :iban_from')
            ->orWhere('t.ibanTo = :iban_to')
            ->setParameter('iban_from', $account->getIban())
            ->setParameter('iban_to', $account->getIban())
            ->getQuery()
            ->getResult();
    }
}
