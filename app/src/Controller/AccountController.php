<?php
namespace App\Controller;

use App\Entity\User;
use App\Services\CurrencyService;
use App\Services\TransactionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AccountController extends AbstractController
{

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function accountView(TransactionService $transactionService)
    {
        /** @var User $user */
        $user         = $this->getUser();
        $account      = $user->getAccount();

        $formattedBalance = CurrencyService::formatMoney(
            $account->getBalance()
        );

        $transactions = $transactionService->findAccountTransactions($account);

        return $this->render(
            'account/view.html.twig',
            [
                'account'       => $account,
                'total_balance' => $formattedBalance,
                'transactions'  => $transactions
            ]
        );
    }

}