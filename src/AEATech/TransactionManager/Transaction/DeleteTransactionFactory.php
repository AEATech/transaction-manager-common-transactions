<?php
declare(strict_types=1);

namespace AEATech\TransactionManager\Transaction;

class DeleteTransactionFactory
{
    public function __construct(
        private readonly IdentifierQuoterInterface $quoter
    ) {
    }

    public function factory(
        string $tableName,
        string $identifierColumn,
        mixed $identifierColumnType, # Doctrine/DBAL type or PDO::PARAM_*
        array $identifiers,
        bool $isIdempotent = true,
    ): DeleteTransaction {
        return new DeleteTransaction(
            $this->quoter,
            $tableName,
            $identifierColumn,
            $identifierColumnType,
            $identifiers,
            $isIdempotent
        );
    }
}
