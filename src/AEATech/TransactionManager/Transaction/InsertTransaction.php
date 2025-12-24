<?php
declare(strict_types=1);

namespace AEATech\TransactionManager\Transaction;

use AEATech\TransactionManager\StatementReusePolicy;
use AEATech\TransactionManager\Transaction\Internal\InsertValuesBuilder;
use AEATech\TransactionManager\Query;
use AEATech\TransactionManager\TransactionInterface;

class InsertTransaction implements TransactionInterface
{
    /**
     * @param InsertValuesBuilder $insertValuesBuilder
     * @param IdentifierQuoterInterface $quoter
     * @param string $tableName e.g. 'users'
     * @param array<array<string, mixed>> $rows
     *        [
     *            ['name' => 'Alex', 'age' => 30],
     *            ['name' => 'Bob', 'age' => 25],
     *        ]
     * @param array<string, mixed> $columnTypes Doctrine/DBAL type or PDO::PARAM_*
     * @param bool $isIdempotent
     * @param StatementReusePolicy $statementReusePolicy
     */
    public function __construct(
        private readonly InsertValuesBuilder $insertValuesBuilder,
        private readonly IdentifierQuoterInterface $quoter,
        private readonly string $tableName,
        private readonly array $rows,
        private readonly array $columnTypes = [],
        private readonly bool $isIdempotent = false,
        private readonly StatementReusePolicy $statementReusePolicy = StatementReusePolicy::None,
    ) {
    }

    public function build(): Query
    {
        [$valuesSql, $params, $types, $columns] = $this->insertValuesBuilder->build($this->rows, $this->columnTypes);

        $quotedColumns = $this->quoter->quoteIdentifiers($columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES %s',
            $this->quoter->quoteIdentifier($this->tableName),
            implode(', ', $quotedColumns),
            $valuesSql,
        );

        return new Query($sql, $params, $types, $this->statementReusePolicy);
    }

    public function isIdempotent(): bool
    {
        return $this->isIdempotent;
    }
}
