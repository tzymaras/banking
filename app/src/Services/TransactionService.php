<?php
namespace App\Services;

use App\Entity\Account;
use App\Entity\Transaction;
use App\Repository\TransactionRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\GuzzleException;

class TransactionService
{
    private TransactionRepository  $transactionRepository;
    private EntityManagerInterface $entityManager;
    private AccountService         $accountService;
    private TransactionHttpClient $transactionHttpClient;

    /**
     * @param TransactionRepository  $transactionRepository
     * @param AccountService         $accountService
     * @param EntityManagerInterface $entityManager
     * @param TransactionHttpClient  $transactionHttpClient
     */
    public function __construct(
        TransactionRepository $transactionRepository,
        AccountService $accountService,
        EntityManagerInterface $entityManager,
        TransactionHttpClient $transactionHttpClient
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->entityManager         = $entityManager;
        $this->accountService        = $accountService;
        $this->transactionHttpClient = $transactionHttpClient;
    }

    /**
     * @param Account $account
     * @return Transaction[]|array
     */
    public function findAccountTransactions(Account $account): array
    {
        $transactions = $this->transactionRepository->findAccountTransactions($account);

        return \array_map(function (Transaction $transaction) {
            return $transaction->setFormattedAmount(
                CurrencyService::formatMoney($transaction->getAmount())
            );
        }, $transactions);
    }

    /**
     * @param Transaction $transaction
     * @param Account     $fromAccount
     * @throws \Doctrine\DBAL\Exception
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \Throwable
     */
    public function processTransferTransaction(Transaction $transaction, Account $fromAccount)
    {
        $transaction->setIbanFrom($fromAccount->getIban());
        $transaction->setType(Transaction::TRANSACTION_TYPE_EXPENSE);
        $transaction->setWorkflowType($this->determineWorkflowType($transaction));
        $transaction->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Berlin')));

        if ($transaction->getWorkflowType() === Transaction::TRANSACTION_WORKFLOW_INTERNAL) {
            $toAccount = $this->accountService->findByIBAN($transaction->getIbanTo());
            $this->processInternalTransaction($transaction, $fromAccount, $toAccount);
            return;
        }

        $this->processExternalTransaction($transaction, $fromAccount, $transaction->getIbanTo());
    }

    /**
     * @param Transaction $transaction
     * @param Account     $toAccount
     * @throws GuzzleException
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \JsonException
     * @throws \Throwable
     */
    public function processDepositTransaction(Transaction $transaction, Account $toAccount)
    {
        $transaction->setIbanTo($toAccount->getIban());
        $transaction->setType(Transaction::TRANSACTION_TYPE_INCOME);
        $transaction->setWorkflowType($this->determineWorkflowType($transaction));
        $transaction->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Berlin')));

        if ($transaction->getWorkflowType() === Transaction::TRANSACTION_WORKFLOW_INTERNAL) {
            $fromAccount = $this->accountService->findByIBAN($transaction->getIbanFrom());
            $this->processInternalTransaction($transaction, $fromAccount, $toAccount);
            return;
        }

        $this->processExternalTransaction($transaction, $toAccount, $transaction->getIbanFrom());
    }

    /**
     * @param Transaction $transaction
     * @param Account     $fromAccount
     * @param Account     $toAccount
     * @throws \Doctrine\DBAL\Exception
     * @throws \Throwable
     */
    protected function processInternalTransaction(Transaction $transaction, Account $fromAccount, Account $toAccount)
    {
        $this->entityManager->getConnection()->beginTransaction();

        try {
            $this->transferAmount($toAccount, $fromAccount, $transaction);
            $this->entityManager->getConnection()->commit();
        } catch (\Throwable $throwable) {
            $this->entityManager->rollback();
            throw $throwable;
        }
    }

    /**
     * @param Transaction $transaction
     * @param Account     $account
     * @param string      $iban
     * @throws GuzzleException
     * @throws \Doctrine\DBAL\Exception
     * @throws \JsonException
     * @throws \Throwable
     */
    protected function processExternalTransaction(Transaction $transaction, Account $account, string $iban)
    {
        $this->transactionHttpClient->makeTransactionRequest([
            'amount' => $transaction->getAmount(),
            'iban'   => $iban
        ]);

        $this->entityManager->getConnection()->beginTransaction();

        try {
            $lockedAccount = $this->entityManager->find(Account::class, $account->getId(), LockMode::PESSIMISTIC_WRITE);

            if ($transaction->getType() === Transaction::TRANSACTION_TYPE_INCOME) {
                $this->accountService->addAmountToAccount($lockedAccount, $transaction->getAmount());
            } else {
                $this->accountService->subtractAmountFromAccount($lockedAccount, $transaction->getAmount());
            }

            $this->entityManager->persist($lockedAccount);
            $this->entityManager->persist($transaction);
            $this->entityManager->flush();

            $this->entityManager->getConnection()->commit();
        } catch (\Throwable $throwable) {
            $this->entityManager->rollback();
            throw $throwable;
        }
    }

    /**
     * @param Transaction $transaction
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @return string
     */
    protected function determineWorkflowType(Transaction $transaction): string
    {
        $iban = $transaction->getType() === Transaction::TRANSACTION_TYPE_EXPENSE
            ? $transaction->getIbanTo()
            : $transaction->getIbanFrom();

        return $this->accountService->accountExists($iban)
            ? Transaction::TRANSACTION_WORKFLOW_INTERNAL
            : Transaction::TRANSACTION_WORKFLOW_EXTERNAL;
    }

    /**
     * @param Account     $toAccount
     * @param Account     $fromAccount
     * @param Transaction $transaction
     */
    public function transferAmount(Account $toAccount, Account $fromAccount, Transaction $transaction)
    {
        $lockedFromAccount = $this->entityManager->find(
            Account::class,
            $fromAccount->getId(),
            LockMode::PESSIMISTIC_WRITE
        );

        $lockedToAccount = $this->entityManager->find(
            Account::class,
            $toAccount->getId(),
            LockMode::PESSIMISTIC_WRITE
        );

        $this->accountService->subtractAmountFromAccount($lockedFromAccount, $transaction->getAmount());
        $this->accountService->addAmountToAccount($lockedToAccount, $transaction->getAmount());

        $this->entityManager->persist($lockedFromAccount);
        $this->entityManager->persist($lockedToAccount);
        $this->entityManager->persist($transaction);

        $this->entityManager->flush();
    }

}