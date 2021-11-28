<?php
namespace App\Services;

use App\Entity\Account;
use App\Entity\User;
use App\Repository\AccountRepository;
use Doctrine\ORM\EntityManagerInterface;

class AccountService
{

    private AccountRepository      $accountRepository;
    private EntityManagerInterface $entityManager;

    /**
     * @param AccountRepository      $accountRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(AccountRepository $accountRepository, EntityManagerInterface $entityManager)
    {
        $this->accountRepository = $accountRepository;
        $this->entityManager     = $entityManager;
    }

    /**
     * @param Account $account
     * @param User    $user
     */
    public function createAccount(Account $account, User $user)
    {
        $this->store($account);

        $user->setAccount($account);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * @param string $iban
     * @return Account|null
     */
    public function findByIBAN(string $iban): ?Account
    {
        return $this->accountRepository->findOneBy(['iban' => $iban]);
    }

    /**
     * @param Account $account
     */
    public function store(Account $account)
    {
        $this->entityManager->persist($account);
        $this->entityManager->flush($account);
    }

    /**
     * @param string $iban
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @return bool
     */
    public function accountExists(string $iban): bool
    {
        return $this->accountRepository->findOneByIBAN($iban) !== null;

    }

    /**
     * @param Account $account
     * @param int     $amount
     */
    public function subtractAmountFromAccount(Account $account, int $amount)
    {
        $account->setBalance($account->getBalance() - $amount);
    }

    /**
     * @param Account $account
     * @param int     $amount
     */
    public function addAmountToAccount(Account $account, int $amount)
    {
        $account->setBalance($account->getBalance() + $amount);
    }

}