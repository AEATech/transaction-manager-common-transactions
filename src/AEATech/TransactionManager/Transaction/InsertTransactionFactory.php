<?php
declare(strict_types=1);

namespace AEATech\TransactionManager\Transaction;

use AEATech\TransactionManager\StatementReusePolicy;
use AEATech\TransactionManager\Transaction\Internal\InsertValuesBuilder;

class InsertTransactionFactory
{
    public function __construct(
        private readonly InsertValuesBuilder $insertValuesBuilder,
        private readonly IdentifierQuoterInterface $quoter,
    ) {
    }

    /**
     * @param array<array<string, mixed>> $rows
     * @param array<string, int|string> $columnTypes Doctrine/DBAL type or PDO::PARAM_*
     */
    public function factory(
        string $tableName,
        array $rows,
        array $columnTypes = [],
        bool $isIdempotent = false,
        StatementReusePolicy $statementReusePolicy = StatementReusePolicy::None,
    ): InsertTransaction {
        return new InsertTransaction(
            $this->insertValuesBuilder,
            $this->quoter,
            $tableName,
            $rows,
            $columnTypes,
            $isIdempotent,
            $statementReusePolicy
        );
    }
}
