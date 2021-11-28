<?php
namespace App\Exception;

use RuntimeException;

class UnknownTransactionTypeException extends RuntimeException
{
    /**
     * @param string $transactionType
     */
    public function __construct(string $transactionType)
    {
        parent::__construct(sprintf('unknown transaction type:%s', $transactionType));
    }

}