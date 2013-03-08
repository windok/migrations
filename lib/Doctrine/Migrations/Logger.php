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
 * Logger
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       3.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class Logger
{
    const UP = 'up';
    const DOWN = 'down';

    protected $storage;

    public function __construct(LoggerStorageInterface $storage)
    {
        $this->storage = $storage;
    }

    public function logUp($version, $manual = false)
    {
        $data = array(
            'direction' => self::UP,
            'version'   => (string) $version,
            'timestamp' => new \DateTime(),
            'manual'    => (bool) $manual,
        );

        $this->storage->save($data);
    }

    public function logDown($version, $manual = false)
    {
        $data = array(
            'direction' => self::DOWN,
            'version'   => (string) $version,
            'timestamp' => new \DateTime(),
            'manual'    => (bool) $manual,
        );

        $this->storage->save($data);
    }

    public function getCurrentVersion()
    {
        $upLogs = $this->getUpLogs();
        $last = end($upLogs);
        return $last['version'];
    }

    public function getUpLogs()
    {
        $upLogs = array();

        $logs = $this->storage->getLogs();
        foreach ($logs as $log) {
            if (self::UP == $log['direction']) {
                $upLogs[$log['version']] = $log;
            } else {
                unset($upLogs[$log['version']]);
            }
        }

        return $upLogs;
    }
}