# AEATech Transaction Manager --- Common SQL Transactions

This package contains **dialect-agnostic SQL transaction
implementations** shared across different database adapters of the
AEATech Transaction Manager ecosystem.

It provides a unified, reusable foundation for SQL write-operations that
work with **any relational database** as long as a database-specific
identifier quoter is supplied.

Typical adapters are built on top of this package:

-   `aeatech/transaction-manager-mysql`
-   `aeatech/transaction-manager-postgresql`

This package **does not** contain any dialect-specific SQL features
(e.g., `INSERT IGNORE`, `ON DUPLICATE KEY`, `ON CONFLICT`).\
Those features belong to adapter-specific packages.

------------------------------------------------------------------------

## ✨ Features

-   Common SQL transaction types:
    -   `InsertTransaction`
    -   `UpdateTransaction`
    -   `DeleteTransaction`
    -   `UpdateWhenThenTransaction` (multi-row conditional updates)
    -   `SqlTransaction` (raw SQL)
-   Fully decoupled from any SQL dialect
-   Requires an implementation of `IdentifierQuoterInterface`
    -   MySQL → backtick quoter\
    -   PostgreSQL → double-quote quoter\
-   Compatible with **Transaction Manager Core** retry policy, backoff
    strategy, and transactional engine
-   Cleanly testable in isolation

------------------------------------------------------------------------

## 📦 Installation

``` bash
composer require aeatech/transaction-manager-common-transactions
```

------------------------------------------------------------------------

## 🧩 Architecture Overview

Each transaction class builds SQL using:

-   an injected **identifier quoter**, and\
-   a dedicated **builder** (e.g., `InsertValuesBuilder`,
    `UpdateWhenThenDefinitionsBuilder`)

All SQL dialect specifics are delegated to adapter packages.

    transaction-manager-core
           ↑
           │
    transaction-manager-common-transactions
           ↑
           ├──────── transaction-manager-mysql
           └──────── transaction-manager-postgresql

------------------------------------------------------------------------

## 🧪 Running Tests in Docker

### Start containers

``` bash
docker-compose -p aeatech-transaction-manager-common-transactions -f docker/docker-compose.yml up -d --build
```

### Stop and remove containers

``` bash
docker-compose -p aeatech-transaction-manager-common-transactions -f docker/docker-compose.yml down -v
```

------------------------------------------------------------------------

## 📥 Install dependencies (example: PHP 8.2)

``` bash
docker-compose -p aeatech-transaction-manager-common-transactions -f docker/docker-compose.yml exec php-cli-8.2 composer install
```

------------------------------------------------------------------------

## ▶️ Run tests for a specific PHP version

PHP 8.2:

``` bash
docker-compose -p aeatech-transaction-manager-common-transactions -f docker/docker-compose.yml exec php-cli-8.2 vendor/bin/phpunit
```

PHP 8.3:

``` bash
docker-compose -p aeatech-transaction-manager-common-transactions -f docker/docker-compose.yml exec php-cli-8.3 vendor/bin/phpunit
```

PHP 8.4:

``` bash
docker-compose -p aeatech-transaction-manager-common-transactions -f docker/docker-compose.yml exec php-cli-8.4 vendor/bin/phpunit
```

------------------------------------------------------------------------

## 🔄 Run all configured PHP variants

``` bash
for v in 8.2 8.3 8.4 ; do   echo "Testing PHP $v...";   docker-compose -p aeatech-transaction-manager-common-transactions -f docker/docker-compose.yml exec php-cli-$v vendor/bin/phpunit || break; done
```

------------------------------------------------------------------------

## 🧵 Attach interactive shells

PHP 8.2:

``` bash
docker-compose -p aeatech-transaction-manager-common-transactions -f docker/docker-compose.yml exec php-cli-8.2 /bin/bash
```

PHP 8.3:

``` bash
docker-compose -p aeatech-transaction-manager-common-transactions -f docker/docker-compose.yml exec php-cli-8.3 /bin/bash
```

PHP 8.4:

``` bash
docker-compose -p aeatech-transaction-manager-common-transactions -f docker/docker-compose.yml exec php-cli-8.4 /bin/bash
```

------------------------------------------------------------------------

## 🔧 Usage Example

``` php
use AEATech\TransactionManager\Transaction\InsertTransaction;
use AEATech\TransactionManager\MySQL\IdentifierQuoter;
use AEATech\TransactionManager\Transaction\Internal\InsertValuesBuilder;

$transaction = new InsertTransaction(
    insertValuesBuilder: new InsertValuesBuilder(),
    quoter: new IdentifierQuoter(),
    tableName: 'users',
    rows: [
        ['name' => 'Alex', 'age' => 30],
        ['name' => 'Bob',  'age' => 25],
    ],
    columnTypes: ['name' => \PDO::PARAM_STR, 'age' => \PDO::PARAM_INT],
);
```

------------------------------------------------------------------------

## License

This project is licensed under the MIT License. See the [LICENSE](./LICENSE) file for details.