<?php
declare(strict_types=1);

namespace AEATech\Test\TransactionManager\Transaction;

use AEATech\TransactionManager\Transaction\IdentifierQuoterInterface;
use Mockery as m;
use function array_map;

trait IdentifierQuoterTrait
{
    private static function buildIdentifierQuoter(): IdentifierQuoterInterface&m\MockInterface
    {
        $quoter = m::mock(IdentifierQuoterInterface::class);
        $quoter->shouldReceive('quoteIdentifier')
            ->andReturnUsing(static fn (string $identifier) => '`' . str_replace('`', '``', $identifier) . '`');

        $quoter->shouldReceive('quoteIdentifiers')
            ->andReturnUsing(
                static fn (array $identifiers) => array_map(
                    static fn (string $identifier) => '`' . str_replace('`', '``', $identifier) . '`',
                    $identifiers
                )
            );

        return $quoter;
    }
}