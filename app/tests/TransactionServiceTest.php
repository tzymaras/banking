<?php

namespace App\Tests;

use App\Entity\Account;
use App\Entity\Transaction;
use App\Repository\AccountRepository;
use App\Repository\TransactionRepository;
use App\Services\AccountService;
use App\Services\TransactionHttpClient;
use App\Services\TransactionService;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class TransactionServiceTest extends TestCase
{
    public function testBalanceIsModifiedOnTransaction(): void
    {
        $transferAmount     = 10000;
        $fromAccountBalance = 100000;
        $toAccountBalance   = 100000;

        $accountService = new AccountService(
            $this->createMock(AccountRepository::class),
            $this->createMock(EntityManagerInterface::class)
        );

        $entityManagerMock         = $this->createMock(EntityManagerInterface::class);
        $transactionRepositoryMock = $this->createMock(TransactionRepository::class);
        $transactionHttpClientMock = $this->createMock(TransactionHttpClient::class);

        $fromAccount = new Account();
        $fromAccount->setId(1);
        $fromAccount->setBalance($fromAccountBalance);
        $fromAccount->setIban('DE35100110011525789687');

        $toAccount = new Account();
        $toAccount->setId(2);
        $toAccount->setBalance($toAccountBalance);
        $toAccount->setIban('DE08100110011525789688');

        $transaction = new Transaction();
        $transaction->setIbanFrom($fromAccount->getIban());
        $transaction->setIbanTo($toAccount->getIban());
        $transaction->setAmount($transferAmount);

        $entityManagerMock->expects($this->any())
            ->method('find')
            ->withConsecutive(
                ['App\Entity\Account', $fromAccount->getId(), LockMode::PESSIMISTIC_WRITE],
                ['App\Entity\Account', $toAccount->getId(), LockMode::PESSIMISTIC_WRITE],
            )
            ->willReturnOnConsecutiveCalls($fromAccount, $toAccount);

        $transactionService = new TransactionService(
            $transactionRepositoryMock,
            $accountService,
            $entityManagerMock,
            $transactionHttpClientMock
        );

        $transactionService->transferAmount($toAccount, $fromAccount, $transaction);

        $this->assertEquals($fromAccountBalance - $transferAmount, $fromAccount->getBalance());
        $this->assertEquals($toAccountBalance + $transferAmount, $toAccount->getBalance());
    }
}
