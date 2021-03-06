<?php

declare(strict_types=1);

namespace Doctrine\DBAL\Tests\Functional\Ticket;

use Doctrine\DBAL\Tests\FunctionalTestCase;
use function in_array;

/**
 * @group DBAL-752
 */
class DBAL752Test extends FunctionalTestCase
{
    protected function setUp() : void
    {
        parent::setUp();

        $platform = $this->connection->getDatabasePlatform()->getName();

        if (in_array($platform, ['sqlite'])) {
            return;
        }

        $this->markTestSkipped('Related to SQLite only');
    }

    public function testUnsignedIntegerDetection() : void
    {
        $this->connection->exec(<<<SQL
CREATE TABLE dbal752_unsigneds (
    small SMALLINT,
    small_unsigned SMALLINT UNSIGNED,
    medium MEDIUMINT,
    medium_unsigned MEDIUMINT UNSIGNED,
    "integer" INTEGER,
    integer_unsigned INTEGER UNSIGNED,
    big BIGINT,
    big_unsigned BIGINT UNSIGNED
);
SQL
        );

        $schemaManager = $this->connection->getSchemaManager();

        $fetchedTable = $schemaManager->listTableDetails('dbal752_unsigneds');

        self::assertEquals('smallint', $fetchedTable->getColumn('small')->getType()->getName());
        self::assertEquals('smallint', $fetchedTable->getColumn('small_unsigned')->getType()->getName());
        self::assertEquals('integer', $fetchedTable->getColumn('medium')->getType()->getName());
        self::assertEquals('integer', $fetchedTable->getColumn('medium_unsigned')->getType()->getName());
        self::assertEquals('integer', $fetchedTable->getColumn('integer')->getType()->getName());
        self::assertEquals('integer', $fetchedTable->getColumn('integer_unsigned')->getType()->getName());
        self::assertEquals('bigint', $fetchedTable->getColumn('big')->getType()->getName());
        self::assertEquals('bigint', $fetchedTable->getColumn('big_unsigned')->getType()->getName());

        self::assertTrue($fetchedTable->getColumn('small_unsigned')->getUnsigned());
        self::assertTrue($fetchedTable->getColumn('medium_unsigned')->getUnsigned());
        self::assertTrue($fetchedTable->getColumn('integer_unsigned')->getUnsigned());
        self::assertTrue($fetchedTable->getColumn('big_unsigned')->getUnsigned());

        self::assertFalse($fetchedTable->getColumn('small')->getUnsigned());
        self::assertFalse($fetchedTable->getColumn('medium')->getUnsigned());
        self::assertFalse($fetchedTable->getColumn('integer')->getUnsigned());
        self::assertFalse($fetchedTable->getColumn('big')->getUnsigned());
    }
}
