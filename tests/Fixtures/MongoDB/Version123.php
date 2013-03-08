<?php

namespace Fixtures\MongoDB;

use Doctrine\Migrations\Migration\AbstractMongoDBMigration;

class Version123 extends AbstractMongoDBMigration
{
	public function getVersion()
	{
		return 123;
	}

	public function up()
	{
		$this->connection->selectCollection('test', 'test');
	}

	public function down()
	{
	}
}
