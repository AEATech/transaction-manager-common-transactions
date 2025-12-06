<?php
declare(strict_types=1);

namespace AEATech\TransactionManager\Transaction;

use AEATech\TransactionManager\Transaction\Internal\UpdateWhenThenDefinitionsBuilder;

class UpdateWhenThenTransactionFactory
{
    public function __construct(
        private readonly UpdateWhenThenDefinitionsBuilder $updateWhenThenDefinitionsBuilder,
        private readonly IdentifierQuoterInterface $quoter
    ) {
    }

    public function factory(
        string $tableName,
        array $rows,
        string $identifierColumn,
        mixed $identifierColumnType,
        array $updateColumns,
        array $updateColumnTypes = [],
        bool $isIdempotent = true,
    ): UpdateWhenThenTransaction {
        return new UpdateWhenThenTransaction(
            $this->updateWhenThenDefinitionsBuilder,
            $this->quoter,
            $tableName,
            $rows,
            $identifierColumn,
            $identifierColumnType,
            $updateColumns,
            $updateColumnTypes,
            $isIdempotent,
        );
    }
}
