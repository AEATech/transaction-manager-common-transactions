<?php
declare(strict_types=1);

namespace AEATech\TransactionManager\Transaction;

use AEATech\TransactionManager\StatementReusePolicy;

class DeleteTransactionFactory
{
    public function __construct(
        private readonly IdentifierQuoterInterface $quoter
    ) {
    }

    /**
     * @param array<string|int, mixed> $identifiers
     */
    public function factory(
        string $tableName,
        string $identifierColumn,
        mixed $identifierColumnType, # Doctrine/DBAL type or PDO::PARAM_*
        array $identifiers,
        bool $isIdempotent = true,
        StatementReusePolicy $statementReusePolicy = StatementReusePolicy::None,
    ): DeleteTransaction {
        return new DeleteTransaction(
            $this->quoter,
            $tableName,
            $identifierColumn,
            $identifierColumnType,
            $identifiers,
            $isIdempotent,
            $statementReusePolicy
        );
    }
}
