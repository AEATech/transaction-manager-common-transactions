<?php
declare(strict_types=1);

namespace AEATech\TransactionManager\Transaction;

interface IdentifierQuoterInterface
{
    /**
     * Quote a single SQL identifier (table, column, alias, etc.).
     *
     * Example (MySQL):
     *  - id        -> `id`
     *  - user_name -> `user_name`
     */
    public function quoteIdentifier(string $identifier): string;

    /**
     * Quote a list of SQL identifiers.
     *
     * This is just a convenience wrapper around quoteIdentifier(),
     * it does NOT parse or split identifiers by dots. Each element
     * of $identifiers is treated as a single logical identifier.
     *
     * @param string[] $identifiers
     *
     * @return string[] quoted identifiers in the same order
     */
    public function quoteIdentifiers(array $identifiers): array;
}
