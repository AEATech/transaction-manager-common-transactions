<?php
declare(strict_types=1);

namespace AEATech\Test\TransactionManager\Transaction;

use AEATech\TransactionManager\Transaction\UpdateTransaction;
use AEATech\TransactionManager\Transaction\UpdateTransactionFactory;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PDO;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(UpdateTransactionFactory::class)]
class UpdateTransactionFactoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    use IdentifierQuoterTrait;

    private const COLUMN_1 = 'column_1';
    private const COLUMN_2 = 'column_2';

    private const TABLE_NAME = 'tm_update_test';
    private const IDENTIFIER_COLUMN = 'identifier_column';
    private const IDENTIFIER_COLUMN_TYPE = PDO::PARAM_INT;
    private const IDENTIFIERS = [1, 2, 3];
    private const COLUMNS_WITH_VALUES_FOR_UPDATE = [
        self::COLUMN_1 => 'value for update',
        self::COLUMN_2 => 100500,
    ];
    private const COLUMN_TYPES = [
        self::COLUMN_1 => PDO::PARAM_STR,
        self::COLUMN_2 => PDO::PARAM_INT,
    ];
    private const IS_IDEMPOTENT = false;

    #[Test]
    public function factory(): void
    {
        $quoter = self::buildIdentifierQuoter();

        $expected = new UpdateTransaction(
            $quoter,
            self::TABLE_NAME,
            self::IDENTIFIER_COLUMN,
            self::IDENTIFIER_COLUMN_TYPE,
            self::IDENTIFIERS,
            self::COLUMNS_WITH_VALUES_FOR_UPDATE,
            self::COLUMN_TYPES,
            self::IS_IDEMPOTENT,
        );

        $actual = (new UpdateTransactionFactory($quoter))->factory(
            self::TABLE_NAME,
            self::IDENTIFIER_COLUMN,
            self::IDENTIFIER_COLUMN_TYPE,
            self::IDENTIFIERS,
            self::COLUMNS_WITH_VALUES_FOR_UPDATE,
            self::COLUMN_TYPES,
            self::IS_IDEMPOTENT,
        );

        self::assertEquals($expected, $actual);
    }
}
