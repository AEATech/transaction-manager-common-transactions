<?php
declare(strict_types=1);

namespace AEATech\TransactionManager\Transaction;

use AEATech\TransactionManager\Query;
use AEATech\TransactionManager\StatementReusePolicy;
use AEATech\TransactionManager\TransactionInterface;
use InvalidArgumentException;

class DeleteTransaction implements TransactionInterface
{
    public const MESSAGE_IDENTIFIERS_MUST_NOT_BE_EMPTY = 'Identifiers must not be empty.';

    /**
     * @param array<string|int, mixed> $identifiers
     */
    public function __construct(
        private readonly IdentifierQuoterInterface $quoter,
        private readonly string $tableName,
        private readonly string $identifierColumn,
        private readonly mixed $identifierColumnType, # Doctrine/DBAL type or PDO::PARAM_*
        private readonly array $identifiers,
        private readonly bool $isIdempotent = true,
        private readonly StatementReusePolicy $statementReusePolicy = StatementReusePolicy::None,
    ) {
    }

    public function build(): Query
    {
        $identifiersCount = count($this->identifiers);

        if (0 === $identifiersCount) {
            throw new InvalidArgumentException(self::MESSAGE_IDENTIFIERS_MUST_NOT_BE_EMPTY);
        }

        $params = array_values($this->identifiers);
        $types = array_fill(0, $identifiersCount, $this->identifierColumnType);
        $placeholders = array_fill(0, $identifiersCount, '?');

        $sql = sprintf(
            'DELETE FROM %s WHERE %s IN (%s)',
            $this->quoter->quoteIdentifier($this->tableName),
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
