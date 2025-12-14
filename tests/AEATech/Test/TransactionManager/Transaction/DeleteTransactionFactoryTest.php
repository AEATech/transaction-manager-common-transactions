<?php
declare(strict_types=1);

namespace AEATech\Test\TransactionManager\Transaction;

use AEATech\TransactionManager\StatementReusePolicy;
use AEATech\TransactionManager\Transaction\DeleteTransaction;
use AEATech\TransactionManager\Transaction\DeleteTransactionFactory;
use AEATech\TransactionManager\Transaction\IdentifierQuoterInterface;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PDO;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Mockery as m;

#[CoversClass(DeleteTransactionFactory::class)]
class DeleteTransactionFactoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private const TABLE_NAME = 'tm_delete_test';
    private const IDENTIFIER_COLUMN = 'identifier_column';
    private const IDENTIFIER_COLUMN_TYPE = PDO::PARAM_INT;
    private const IDENTIFIERS = [1, 2, 3];
    private const IS_IDEMPOTENT = false;

    #[Test]
    public function factory(): void
    {
        $quoter = m::mock(IdentifierQuoterInterface::class);

        $expected = new DeleteTransaction(
            $quoter,
            self::TABLE_NAME,
            self::IDENTIFIER_COLUMN,
            self::IDENTIFIER_COLUMN_TYPE,
            self::IDENTIFIERS,
            self::IS_IDEMPOTENT,
            StatementReusePolicy::PerTransaction
        );

        $actual = (new DeleteTransactionFactory($quoter))->factory(
            self::TABLE_NAME,
            self::IDENTIFIER_COLUMN,
            self::IDENTIFIER_COLUMN_TYPE,
            self::IDENTIFIERS,
            self::IS_IDEMPOTENT,
            StatementReusePolicy::PerTransaction
        );

        self::assertEquals($expected, $actual);
    }
}
