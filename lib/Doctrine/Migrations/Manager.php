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

/**
 * Manager
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       3.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class Manager
{
    private $logger;
    private $migrations;
    private $executor;

    public function __construct(Logger $logger, Migrations $migrations, Executor $executor)
    {
        $this->logger = $logger;
        $this->migrations = $migrations;
        $this->executor = $executor;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function getMigrations()
    {
        return $this->migrations;
    }

    public function getExecutor()
    {
        return $this->executor;
    }

    public function execute($version, $direction)
    {
        return $this->executor->execute($this->migrations[$version], $direction);;
    }

    public function migrate($version = null)
    {
        if (!$this->migrations->count()) {
            // nothing to do
            return;
        }

        $version = $version !== null ? $version : $this->migrations->getNewest()->getVersion();

        list($forUp, $forDown) = $this->executor->getMigrationsToExecute($this->migrations, $version);

        foreach ($forDown as $migration) {
            $this->executor->execute($migration, Executor::DIRECTION_DOWN);
        }

        foreach ($forUp as $migration) {
            $this->executor->execute($migration, Executor::DIRECTION_UP);
        }
    }

    public function hasVersion($version)
    {
        return isset($this->migrations[$version]);
    }

    public function isVersionMigrated($version)
    {
        $upLogs = $this->logger->getUpLogs();
        return $this->hasVersion($version) && isset($upLogs[$version]);
    }

    public function getCurrentVersion()
    {
        return $this->logger->getCurrentVersion();
    }

    public function getNewestVersion()
    {
        return $this->migrations->getNewest() ? $this->migrations->getNewest()->getVersion() : 0;
    }

    public function getExecutedMigrations()
    {
        $executedMigrations = array();
        $upLogs = $this->logger->getUpLogs();
        foreach ($upLogs as $version => $upLog) {
            $executedMigrations[$version] = $this->migrations[$version];
        }

        return $executedMigrations;
    }
}
