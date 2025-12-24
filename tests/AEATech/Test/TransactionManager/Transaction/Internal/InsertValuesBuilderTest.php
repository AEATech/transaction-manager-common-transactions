<?php
declare(strict_types=1);

namespace AEATech\Test\TransactionManager\Transaction\Internal;

use AEATech\TransactionManager\Transaction\Internal\InsertValuesBuilder;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PDO;

#[CoversClass(InsertValuesBuilder::class)]
class InsertValuesBuilderTest extends TestCase
{
    #[Test]
    public function buildReturnsSqlParamsTypesAndColumnsForMultipleRows(): void
    {
        $rows = [
            ['id' => 1, 'name' => 'Alex', 'age' => 30],
            ['id' => 2, 'name' => 'Bob',  'age' => 25],
        ];

        $columnTypes = [
            'id' => PDO::PARAM_INT,
            'age' => PDO::PARAM_INT,
            // name intentionally omitted to ensure it is not added to types
        ];

        $builder = new InsertValuesBuilder();
        [$valuesSql, $params, $types, $columns] = $builder->build($rows, $columnTypes);

        self::assertSame('(?, ?, ?), (?, ?, ?)', $valuesSql);
        self::assertSame([1, 'Alex', 30, 2, 'Bob', 25], $params);
        self::assertSame(['id', 'name', 'age'], $columns);

        // Only explicitly typed columns should appear in $types mapped by param position
        self::assertSame([
            0 => PDO::PARAM_INT, // id of row 1
            2 => PDO::PARAM_INT, // age of row 1
            3 => PDO::PARAM_INT, // id of row 2
            5 => PDO::PARAM_INT, // age of row 2
        ], $types);
    }

    #[Test]
    public function preservesColumnsOrderFromFirstRow(): void
    {
        $rows = [
            ['a' => 'A1', 'b' => 'B1', 'c' => 'C1'],
            // different order in the second row should not affect placeholders order
            ['c' => 'C2', 'a' => 'A2', 'b' => 'B2'],
        ];

        $builder = new InsertValuesBuilder();
        [$valuesSql, $params, $types, $columns] = $builder->build($rows, []);

        self::assertSame('(?, ?, ?), (?, ?, ?)', $valuesSql);
        self::assertSame(['A1', 'B1', 'C1', 'A2', 'B2', 'C2'], $params);
        self::assertSame([], $types);
        self::assertSame(['a', 'b', 'c'], $columns);
    }

    #[Test]
    public function missingRequiredColumnThrows(): void
    {
        $rows = [
            ['id' => 1, 'name' => 'A'],
            // missing name
            ['id' => 2],
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('missing required column "name"');
        (new InsertValuesBuilder())->build($rows, []);
    }

    #[Test]
    public function nonArrayRowThrows(): void
    {
        $rows = [
            ['x' => 1],
            'oops', // not an array
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('row 1 must be an array, string given');
        /** @noinspection PhpParamsInspection */
        (new InsertValuesBuilder())->build($rows, []); //@phpstan-ignore-line
    }

    #[Test]
    public function emptyRowsThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('requires non-empty $rows');
        (new InsertValuesBuilder())->build([], []);
    }

    #[Test]
    public function emptyFirstRowThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('first row must be a non-empty array');
        (new InsertValuesBuilder())->build([[]], []);
    }

    #[Test]
    public function nonArrayFirstRowThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('first row must be a non-empty array');

        /** @noinspection PhpParamsInspection */
        (new InsertValuesBuilder())->build([123], []); //@phpstan-ignore-line
    }
}
