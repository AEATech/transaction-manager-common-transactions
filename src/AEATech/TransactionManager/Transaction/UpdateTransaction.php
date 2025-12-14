<?php
declare(strict_types=1);

namespace AEATech\TransactionManager\Transaction;

use AEATech\TransactionManager\Query;
use AEATech\TransactionManager\StatementReusePolicy;
use AEATech\TransactionManager\TransactionInterface;
use InvalidArgumentException;

class UpdateTransaction implements TransactionInterface
{
    public const MESSAGE_IDENTIFIERS_MUST_NOT_BE_EMPTY = 'Identifiers must not be empty.';
    public const MESSAGE_COLUMNS_WITH_VALUES_FOR_UPDATE_MUST_NOT_BE_EMPTY
        = 'Columns with values for update must not be empty.';

    public function __construct(
        private readonly IdentifierQuoterInterface $quoter,
        private readonly string $tableName,
        private readonly string $identifierColumn,
        private readonly mixed $identifierColumnType, # Doctrine/DBAL type or PDO::PARAM_*
        private readonly array $identifiers,
        private readonly array $columnsWithValuesForUpdate,
        private readonly array $columnTypes = [], # Doctrine/DBAL type or PDO::PARAM_*
        private readonly bool $isIdempotent = true,
        private readonly StatementReusePolicy $statementReusePolicy = StatementReusePolicy::None,
    ) {
    }

    public function build(): Query
    {
        if (empty($this->identifiers)) {
            throw new InvalidArgumentException(self::MESSAGE_IDENTIFIERS_MUST_NOT_BE_EMPTY);
        }

        if (empty($this->columnsWithValuesForUpdate)) {
            throw new InvalidArgumentException(self::MESSAGE_COLUMNS_WITH_VALUES_FOR_UPDATE_MUST_NOT_BE_EMPTY);
        }

        $paramIndex = 0;
        $params = [];
        $types = [];
        $updateSetParts = [];

        foreach ($this->columnsWithValuesForUpdate as $column => $value) {
            $quotedColumn = $this->quoter->quoteIdentifier($column);

            $updateSetParts[] = sprintf('%s = ?', $quotedColumn);

            $params[$paramIndex] = $value;

            if (isset($this->columnTypes[$column])) {
                $types[$paramIndex] = $this->columnTypes[$column];
            }

            $paramIndex++;
        }

        $placeholders = [];

        foreach ($this->identifiers as $identifier) {
            $params[$paramIndex] = $identifier;
            $types[$paramIndex] = $this->identifierColumnType;
            $placeholders[] = '?';

            $paramIndex++;
        }

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s IN (%s)',
            $this->quoter->quoteIdentifier($this->tableName),
            implode(', ', $updateSetParts),
            $this->quoter->quoteIdentifier($this->identifierColumn),
            implode(', ', $placeholders),
        );

        return new Query($sql, $params, $types, $this->statementReusePolicy);
    }

    public function isIdempotent(): bool
    {
        return $this->isIdempotent;
    }
}
