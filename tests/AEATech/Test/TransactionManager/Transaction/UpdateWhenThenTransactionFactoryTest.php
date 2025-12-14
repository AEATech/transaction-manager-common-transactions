<?php
declare(strict_types=1);

namespace AEATech\Test\TransactionManager\Transaction;

use AEATech\TransactionManager\StatementReusePolicy;
use AEATech\TransactionManager\Transaction\Internal\UpdateWhenThenDefinitionsBuilder;
use AEATech\TransactionManager\Transaction\UpdateWhenThenTransaction;
use AEATech\TransactionManager\Transaction\UpdateWhenThenTransactionFactory;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PDO;

#[CoversClass(UpdateWhenThenTransactionFactory::class)]
class UpdateWhenThenTransactionFactoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    use IdentifierQuoterTrait;

    private const COLUMN_1 = 'column_1';
    private const COLUMN_2 = 'column_2';
    private const TABLE_NAME = 'tm_update_test';
    private const ROWS = [
        [
            self::IDENTIFIER_COLUMN => 1,
            self::COLUMN_1 => '10',
            self::COLUMN_2 => 100,
        ],
        [
            self::IDENTIFIER_COLUMN => 2,
            self::COLUMN_1 => '20',
            self::COLUMN_2 => 200,
        ],
    ];
    private const IDENTIFIER_COLUMN = 'identifier_column';
    private const IDENTIFIER_COLUMN_TYPE = PDO::PARAM_INT;
    private const UPDATE_COLUMNS = [
        self::COLUMN_1,
        self::COLUMN_2,
    ];
    private const UPDATE_COLUMN_TYPES = [
        self::COLUMN_1 => PDO::PARAM_STR,
        self::COLUMN_2 => PDO::PARAM_INT,
    ];
    private const IS_IDEMPOTENT = false;

    #[Test]
    public function factory(): void
    {
        $updateWhenThenDefinitionsBuilder = Mockery::mock(UpdateWhenThenDefinitionsBuilder::class);
        $quoter = self::buildIdentifierQuoter();

        $expected = new UpdateWhenThenTransaction(
            $updateWhenThenDefinitionsBuilder,
            $quoter,
            self::TABLE_NAME,
            self::ROWS,
            self::IDENTIFIER_COLUMN,
            self::IDENTIFIER_COLUMN_TYPE,
            self::UPDATE_COLUMNS,
            self::UPDATE_COLUMN_TYPES,
            self::IS_IDEMPOTENT,
            StatementReusePolicy::PerTransaction
        );

        $actual = (new UpdateWhenThenTransactionFactory($updateWhenThenDefinitionsBuilder, $quoter))
            ->factory(
                self::TABLE_NAME,
                self::ROWS,
                self::IDENTIFIER_COLUMN,
                self::IDENTIFIER_COLUMN_TYPE,
                self::UPDATE_COLUMNS,
                self::UPDATE_COLUMN_TYPES,
                self::IS_IDEMPOTENT,
                StatementReusePolicy::PerTransaction
            );

        self::assertEquals($expected, $actual);
    }
}
