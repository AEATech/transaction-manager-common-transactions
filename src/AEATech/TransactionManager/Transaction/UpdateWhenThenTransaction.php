<?php
declare(strict_types=1);

namespace AEATech\TransactionManager\Transaction;

use AEATech\TransactionManager\Query;
use AEATech\TransactionManager\StatementReusePolicy;
use AEATech\TransactionManager\Transaction\Internal\UpdateWhenThenDefinitionsBuilder;
use AEATech\TransactionManager\TransactionInterface;

class UpdateWhenThenTransaction implements TransactionInterface
{
    /**
     * @param array<array<string, mixed>> $rows
     * @param string[] $updateColumns
     * @param array<string, mixed> $updateColumnTypes
     */
    public function __construct(
        private readonly UpdateWhenThenDefinitionsBuilder $definitionsBuilder,
        private readonly IdentifierQuoterInterface $quoter,
        private readonly string $tableName,
        private readonly array $rows,
        private readonly string $identifierColumn,
        private readonly mixed $identifierColumnType,
        private readonly array $updateColumns,
        private readonly array $updateColumnTypes = [],
        private readonly bool $isIdempotent = true,
        private readonly StatementReusePolicy $statementReusePolicy = StatementReusePolicy::None
    ) {
    }

    public function build(): Query
    {
        [
            $identifiers,
            $updateDefinitions,
        ] = $this->definitionsBuilder->build($this->rows, $this->identifierColumn, $this->updateColumns);

        $quotedIdentifierColumn = $this->quoter->quoteIdentifier($this->identifierColumn);
        $whenThenPart = sprintf('WHEN %s = ? THEN ?', $quotedIdentifierColumn);
        $params = [];
        $types = [];
        $setCaseParts = [];

        foreach ($updateDefinitions as $column => $values) {
            $quotedColumn = $this->quoter->quoteIdentifier($column);
            $columnType = $this->updateColumnTypes[$column] ?? null;

            $whenThenParts = [];
            foreach ($values as [$identifier, $value]) {
                $whenThenParts[] = $whenThenPart;

                $params[] = $identifier;
                $types[] = $this->identifierColumnType;

                $params[] = $value;
                $types[] = $columnType;
            }

            $setCaseParts[] = sprintf(
                '%s = CASE %s ELSE %s END',
                $quotedColumn,
                implode(' ', $whenThenParts),
                $quotedColumn
            );
        }

        $placeholders = [];

        foreach ($identifiers as $identifier) {
            $params[] = $identifier;
            $types[] = $this->identifierColumnType;
            $placeholders[] = '?';
        }

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s IN (%s)',
            $this->quoter->quoteIdentifier($this->tableName),
            implode(', ', $setCaseParts),
            $quotedIdentifierColumn,
            implode(', ', $placeholders),
        );

        $types = array_filter($types);

        return new Query($sql, $params, $types, $this->statementReusePolicy);
    }

    public function isIdempotent(): bool
    {
        return $this->isIdempotent;
    }
}
