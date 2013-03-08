<?php

namespace Fixtures\Agnostic;

use Doctrine\Migrations\AbstractMigration;

class Version123 extends AbstractMigration
{
	public function getVersion()
	{
		return 123;
	}

	public function up()
	{
	}

	public function down()
	{
	}
}
