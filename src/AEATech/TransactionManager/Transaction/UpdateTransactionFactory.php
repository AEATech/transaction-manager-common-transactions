<?php
declare(strict_types=1);

namespace AEATech\TransactionManager\Transaction;

class UpdateTransactionFactory
{
    public function __construct(
        private readonly IdentifierQuoterInterface $quoter
    ) {
    }

    public function factory(
        string $tableName,
        string $identifierColumn,
        mixed $identifierColumnType,
        array $identifiers,
        array $columnsWithValuesForUpdate,
        array $columnTypes = [],
        bool $isIdempotent = true,
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
        );
    }
}
