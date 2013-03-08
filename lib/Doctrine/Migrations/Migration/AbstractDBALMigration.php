<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
*/

namespace Doctrine\Migrations\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\Migrations\Notifier;
use Doctrine\Migrations\AbstractMigration;

abstract class AbstractDBALMigration extends AbstractMigration
{
    private $connection;
    private $sql = array();
    private $params = array();
    private $types = array();

    public function __construct(Notifier $nofitier, Connection $connection)
    {
        $this->notifier = $nofitier;
        $this->connection = $connection;
    }

    /**
     * Add some SQL queries to this versions migration
     *
     * @param array|string $sql
     * @param array        $params
     * @param array        $types
     *
     * @return void
     */
    public function addSql($sql, array $params = array(), array $types = array())
    {
        if (is_array($sql)) {
            foreach ($sql as $key => $query) {
                $this->sql[] = $query;
                if (isset($params[$key])) {
                    $this->params[count($this->sql) - 1] = $params[$key];
                    $this->types[count($this->sql) - 1] = isset($types[$key]) ? $types[$key] : array();
                }
            }
        } else {
            $this->sql[] = $sql;
            if ($params) {
                $this->params[count($this->sql) - 1] = $params;
                $this->types[count($this->sql) - 1] = $types ?: array();
            }
        }
    }

    public function execute()
    {
        try {
            foreach ($this->sql as $key => $query) {
                if ( ! isset($this->params[$key])) {
                    $this->notifier->write('     <comment>-></comment> ' . $query);
                    $this->connection->executeQuery($query);
                } else {
                    $this->notifier->write(sprintf('    <comment>-</comment> %s (with parameters)', $query));
                    $this->connection->executeQuery($query, $this->params[$key], $this->types[$key]);
                }
            }
        } catch (\Exception $e) {
            $this->notifier->write(sprintf('<warning>%s</warning>', $e->getMessage()));
            $this->connection->rollback();
        }

        $this->sql = array();
        $this->params = array();
        $this->types = array();
    }

    public function preExecute()
    {
        $this->connection->beginTransaction();
    }

    public function postExecute()
    {
        if ($this->connection->isTransactionActive()) {
            $this->connection->commit();
        }
    }
}
