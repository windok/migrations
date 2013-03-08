<?php
/**
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

namespace Doctrine\Migrations;

use Doctrine\DBAL\Migrations\Configuration\Configuration;

/**
 * Version executor.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       3.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class Executor
{
    const DIRECTION_UP = 'up';
    const DIRECTION_DOWN = 'down';

    private $logger;

    public function __construct(Logger $logger, OutputWriter $outputWriter)
    {
        $this->logger = $logger;
        $this->outputWriter = $outputWriter;
    }

    public function getMigrationsToExecute(Migrations $migrations, $targetVersion)
    {
        $currentlyUp = $this->logger->getUpLogs();

        $forUp = array();
        $forDown = array();
        foreach ($migrations as $version => $migration) {
            if ($version <= $targetVersion && !isset($currentlyUp[$version])) {
                $forUp[$version] = $migration;
            } elseif ($version > $targetVersion && isset($currentlyUp[$version])) {
                $forDown[$version] = $migration;
            }
        }

        ksort($forUp);
        krsort($forDown);

        return array($forUp, $forDown);
    }

    /**
     * Execute this migration version up or down and and return the SQL.
     *
     * @param string  $direction The direction to execute the migration.
     *
     * @return array $sql
     *
     * @throws \Exception when migration fails
     */
    public function execute(AbstractMigration $migration, $direction)
    {
        try {
            $start = microtime(true);

            $migration->setState(AbstractMigration::STATE_PRE);
            $migration->{'pre' . ucfirst($direction)}();

            if ($direction === self::DIRECTION_UP) {
                $this->outputWriter->write("\n" . sprintf('  <info>++</info> migrating <comment>%s</comment>', $migration->getVersion()) . "\n");
            } else {
                $this->outputWriter->write("\n" . sprintf('  <info>--</info> reverting <comment>%s</comment>', $migration->getVersion()) . "\n");
            }

            $migration->setState(AbstractMigration::STATE_EXEC);

            $migration->$direction();
            $migration->preExecute();
            $migration->execute();

            if ($direction === self::DIRECTION_UP) {
                $this->logger->logUp($migration->getVersion());
            } else {
                $this->logger->logDown($migration->getVersion());
            }

            $migration->setState(AbstractMigration::STATE_POST);
            $migration->{'post' . ucfirst($direction)}();
            $migration->postExecute();

            $end = microtime(true);
            $time = round($end - $start, 2);
            if ($direction === self::DIRECTION_UP) {
                $this->outputWriter->write(sprintf("\n  <info>++</info> migrated (%ss)", $time));
            } else {
                $this->outputWriter->write(sprintf("\n  <info>--</info> reverted (%ss)", $time));
            }

            $migration->setState(AbstractMigration::STATE_NONE);

        } catch (SkipMigrationException $e) {

            // now mark it as migrated
            if ($direction === self::DIRECTION_UP) {
                $this->logger->logUp($migration->getVersion());
            } else {
                $this->logger->logDown($migration->getVersion());
            }

            $this->outputWriter->write(sprintf("\n  <info>SS</info> skipped (Reason: %s)",  $e->getMessage()));

            $migration->setState(AbstractMigration::STATE_NONE);

        } catch (\Exception $e) {

            $this->outputWriter->write(sprintf(
                '<error>Migration %s failed during %s. Error %s</error>',
                $migration->getVersion(), Tools::getExecutionState($migration->getState()), $e->getMessage()
            ));

            $migration->setState(AbstractMigration::STATE_NONE);

            throw $e;
        }
    }
}
