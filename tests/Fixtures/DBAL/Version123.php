<?php

namespace Fixtures\DBAL;

use Doctrine\Migrations\Migration\AbstractDBALMigration;

class Version123 extends AbstractDBALMigration
{
	public function getVersion()
	{
		return 123;
	}

	public function up()
	{
		$this->addSql('INSERT INTO users (username) VALUES (?)', array('username'));
	}

	public function down()
	{
		$this->addSql('DELETE FROM users WHERE username = ?', array('username'));
	}
}
