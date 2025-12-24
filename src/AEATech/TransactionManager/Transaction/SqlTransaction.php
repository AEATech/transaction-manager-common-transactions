<?php
declare(strict_types=1);

namespace AEATech\TransactionManager\Transaction;

use AEATech\TransactionManager\Query;
use AEATech\TransactionManager\StatementReusePolicy;
use AEATech\TransactionManager\TransactionInterface;

class SqlTransaction implements TransactionInterface
{
    /**
     * @param array<string|int, mixed> $params
     * @param array<string, mixed> $types
     */
    public function __construct(
        private readonly string $sql,
        private readonly array $params = [],
        private readonly array $types = [],
        private readonly bool $isIdempotent = false,
        private readonly StatementReusePolicy $statementReusePolicy = StatementReusePolicy::None
    ) {
    }

    public function build(): Query
    {
        return new Query($this->sql, $this->params, $this->types, $this->statementReusePolicy);
    }

    public function isIdempotent(): bool
    {
        return $this->isIdempotent;
    }
}
