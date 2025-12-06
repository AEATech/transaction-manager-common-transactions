<?php
declare(strict_types=1);

namespace AEATech\Test\TransactionManager\Transaction;

use AEATech\TransactionManager\Transaction\IdentifierQuoterInterface;
use AEATech\TransactionManager\Transaction\InsertTransaction;
use AEATech\TransactionManager\Transaction\Internal\InsertValuesBuilder;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Throwable;

#[CoversClass(InsertTransaction::class)]
class InsertTransactionTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    use IdentifierQuoterTrait;

    private InsertValuesBuilder&m\MockInterface $insertValuesBuilder;
    private IdentifierQuoterInterface&m\MockInterface $quoter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->insertValuesBuilder = m::mock(InsertValuesBuilder::class);
        $this->quoter = self::buildIdentifierQuoter();
    }

    /**
     * @throws Throwable
     */
    #[Test]
    public function build(): void
    {
        // Arrange
        $rows = [
            ['id' => 1, 'na`me' => 'Alex'],
        ];

        $this->insertValuesBuilder->shouldReceive('build')
            ->once()
            ->with($rows, ['id' => 1])
            ->andReturn([
                '(?, ?)',                // values SQL
                [1, 'Alex'],             // params
                [0 => 1],                // types (simplified map for the test)
                ['id', 'na`me'],         // columns to be quoted by transaction
            ]);

        $tx = new InsertTransaction($this->insertValuesBuilder, $this->quoter, 'users', $rows, ['id' => 1], true);

        // Act
        $q = $tx->build();

        // Assert
        self::assertSame('INSERT INTO `users` (`id`, `na``me`) VALUES (?, ?)', $q->sql);
        self::assertSame([1, 'Alex'], $q->params);
        self::assertSame([0 => 1], $q->types);
        self::assertTrue($tx->isIdempotent());
    }

    /**
     * @throws Throwable
     */
    #[Test]
    #[DataProvider('isIdempotentDataProvider')]
    public function isIdempotent(bool $isIdempotent): void
    {
        $rows = [['a' => 1]];
        $this->insertValuesBuilder->shouldReceive('build')->andReturn(['(?)', [1], [], ['a']]);

        $insertTransaction = new InsertTransaction(
            $this->insertValuesBuilder,
            $this->quoter,
            't',
            $rows,
            [],
            $isIdempotent
        );

        self::assertSame($isIdempotent, $insertTransaction->isIdempotent());
    }

    public static function isIdempotentDataProvider(): array
    {
        return [
            [
                'isIdempotent' => true,
            ],
            [
                'isIdempotent' => false,
            ]
        ];
    }
}
