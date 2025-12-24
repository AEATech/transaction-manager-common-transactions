<?php
declare(strict_types=1);

namespace AEATech\TransactionManager\Transaction;

use AEATech\TransactionManager\StatementReusePolicy;

class UpdateTransactionFactory
{
    public function __construct(
        private readonly IdentifierQuoterInterface $quoter
    ) {
    }

    /**
     * @param array<string|int, mixed> $identifiers
     * @param array<string, mixed> $columnsWithValuesForUpdate
     * @param array<string, mixed> $columnTypes
     */
    public function factory(
        string $tableName,
        string $identifierColumn,
        mixed $identifierColumnType,
        array $identifiers,
        array $columnsWithValuesForUpdate,
        array $columnTypes = [],
        bool $isIdempotent = true,
        StatementReusePolicy $statementReusePolicy = StatementReusePolicy::None,
    ): UpdateTransaction {
        return new UpdateTransaction(
            $this->quoter,
            $tableName,
            $identifierColumn,
            $identifierColumnType,
            $identifiers,
            $columnsWithValuesForUpdate,
            $columnTypes,
            $isIdempotent,
            $statementReusePolicy
        );
    }
}
