<?php
declare(strict_types=1);

namespace AEATech\Test\TransactionManager\Transaction;

use AEATech\TransactionManager\Query;
use AEATech\TransactionManager\Transaction\DeleteTransaction;
use AEATech\TransactionManager\Transaction\IdentifierQuoterInterface;
use InvalidArgumentException;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PDO;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Mockery as m;

#[CoversClass(DeleteTransaction::class)]
class DeleteTransactionTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    use IdentifierQuoterTrait;

    private IdentifierQuoterInterface&m\MockInterface $quoter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->quoter = self::buildIdentifierQuoter();
    }

    #[Test]
    public function build(): void
    {
        $identifiers = [
            100501 => 1,
            100502 => 2,
            100503 => 3,
        ];

        $transaction = new DeleteTransaction(
            $this->quoter,
            'test_table',
            'id',
            PDO::PARAM_INT,
            $identifiers
        );

        $expectedParams = [];
        $expectedTypes = [];
        foreach ($identifiers as $identifier) {
            $expectedParams[] = $identifier;
            $expectedTypes[] = PDO::PARAM_INT;
        }

        $expectedSql = 'DELETE FROM `test_table` WHERE `id` IN (?, ?, ?)';

        $expectedQuery = new Query($expectedSql, $expectedParams, $expectedTypes);

        /** @noinspection PhpUnhandledExceptionInspection */
        $actualQuery = $transaction->build();

        self::assertEquals($expectedQuery, $actualQuery);
    }

    #[Test]
    #[DataProvider('isIdempotentDataProvider')]
    public function isIdempotent(bool $isIdempotent): void
    {
        $transaction = new DeleteTransaction(
            $this->quoter,
            'test_table',
            'id',
            PDO::PARAM_INT,
            [1, 2, 3],
            $isIdempotent
        );

        self::assertSame($isIdempotent, $transaction->isIdempotent());
    }

    public static function isIdempotentDataProvider(): array
    {
        return [
            [
                'isIdempotent' => true,
            ],
            [
                'isIdempotent' => false,
            ],
        ];
    }

    #[Test]
    public function buildFailedWithEmptyIdentifiers(): void
    {
        $transaction = new DeleteTransaction(
            $this->quoter,
            'test_table',
            'id',
            PDO::PARAM_INT,
            []
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(DeleteTransaction::MESSAGE_IDENTIFIERS_MUST_NOT_BE_EMPTY);

        /** @noinspection PhpUnhandledExceptionInspection */
        $transaction->build();
    }
}
