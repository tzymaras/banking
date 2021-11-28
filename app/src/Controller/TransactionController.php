<?php
namespace App\Controller;

use App\Entity\Account;
use App\Entity\Transaction;
use App\Exception\UnknownTransactionTypeException;
use App\Form\TransactionType;
use App\Services\AccountService;
use App\Services\TransactionService;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class TransactionController extends AbstractController
{

    private AccountService     $accountService;
    private TransactionService $transactionService;
    private Security           $security;
    private LoggerInterface    $logger;

    /**
     * @param AccountService     $accountService
     * @param TransactionService $transactionService
     * @param Security           $security
     * @param LoggerInterface    $logger
     */
    public function __construct(
        AccountService $accountService,
        TransactionService $transactionService,
        Security $security,
        LoggerInterface $logger
    ) {
        $this->accountService     = $accountService;
        $this->transactionService = $transactionService;
        $this->security           = $security;
        $this->logger             = $logger;
    }

    /**
     * @param Request $request
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function transfer(Request $request)
    {
        return $this->processTransaction($request, Transaction::TRANSACTION_TYPE_EXPENSE);
    }

    /**
     * @param Request $request
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function deposit(Request $request)
    {
        return $this->processTransaction($request, Transaction::TRANSACTION_TYPE_INCOME);
    }

    /**
     * @param Request $request
     * @param string  $transactionType
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    private function processTransaction(Request $request, string $transactionType)
    {
        /** @var Account $account */
        $account = $this->security->getUser()->getAccount();

        $transaction = new Transaction();

        $form = $this->createForm(
            TransactionType::class,
            $transaction,
            $this->getDefaultFormOptions($account, $transactionType)
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                switch ($transactionType) {
                    case Transaction::TRANSACTION_TYPE_INCOME:
                        $this->transactionService->processDepositTransaction($transaction, $account);
                        break;
                    case Transaction::TRANSACTION_TYPE_EXPENSE:
                        $this->transactionService->processTransferTransaction($transaction, $account);
                        break;
                    default:
                        throw new UnknownTransactionTypeException($transactionType);
                }
            } catch (ClientExceptionInterface $clientException) {
                $this->addFlash('notice', 'Externe Transaktion fehlgeschlagen');
                return $this->logAndRedirectToAccount('failed request to external service', $clientException);
            } catch (\Throwable $throwable) {
                $this->addFlash('notice', 'Transaktion fehlgeschlagen');
                return $this->logAndRedirectToAccount('failed transaction', $throwable);
            }

            $this->addFlash('success', 'Transaktion erfolgreich durchgeführt');
            return $this->redirectToRoute('account_view');
        }

        return $this->renderForm(
            'account/transaction/transfer.html.twig',
            ['form' => $form, 'transactionType' => $transactionType]
        );
    }

    /**
     * @param string     $logMessage
     * @param \Throwable $throwable
     * @return RedirectResponse
     */
    private function logAndRedirectToAccount(string $logMessage, \Throwable $throwable): RedirectResponse
    {
        $this->logger->error(
            $logMessage,
            ['reason' => $throwable->getMessage(), 'stack_trace' => $throwable->getTraceAsString()]
        );

        return $this->redirectToRoute('account_view');
    }

    /**
     * @param Account $account
     * @param string  $transactionType
     * @return array
     */
    private function getDefaultFormOptions(Account $account, string $transactionType): array
    {
        return [
            TransactionType::OPTION_TRANSACTION_TYPE => $transactionType,
            TransactionType::OPTION_USER_IBAN        => $account->getIban()
        ];
    }

}