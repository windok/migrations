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
 * Migrations
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       3.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class Migrations implements \Countable, \ArrayAccess, \Iterator
{
    private $migrations = array();

    public function getMigrations()
    {
        return $this->migrations;
    }

    public function getNewest()
    {
        return end($this->migrations);
    }

    // Countable

    public function count()
    {
        return count($this->migrations);
    }

    // ArrayAccess

    public function offsetGet($version)
    {
        if (!isset($this->migrations)) {
            throw new \InvalidArgumentException(sprintf('Version %s does not exists.', $version));
        }

        return $this->migrations[$version];
    }

    public function offsetExists($version)
    {
        return isset($this->migrations[$version]);
    }

    public function offsetSet($version, $value)
    {
        $version = $value->getVersion();
        if (isset($this->migrations[$version])) {
            throw MigrationException::duplicateMigrationVersion($version, get_class($this->migrations[$version]));
        }

        $this->migrations[$version] = $value;

        ksort($this->migrations);
    }

    public function offsetUnset($version)
    {
        unset($this->migrations[$version]);
    }

    // Iterator

    public function rewind()
    {
        return reset($this->migrations);
    }

    public function current()
    {
        return current($this->migrations);
    }

    public function key()
    {
        return key($this->migrations);
    }

    public function next()
    {
        return next($this->migrations);
    }

    public function valid()
    {
        return key($this->migrations) !== null;
    }
}
