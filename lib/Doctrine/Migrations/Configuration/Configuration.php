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

namespace Doctrine\Migrations\Configuration;

use Doctrine\Migrations\MigrationException;

/**
 * Default Migration Configurtion object.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       3.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class Configuration
{
    /**
     * Name of this set of migrations
     *
     * @var string
     */
    private $name;

    /**
     * The path to a directory where new migration classes will be written
     *
     * @var string
     */
    private $migrationsDirectory;

    /**
     * Namespace the migration classes live in
     *
     * @var string
     */
    private $migrationsNamespace;

    /**
     * Bootstrap file to load your migrations.
     *
     * @var string
     */
    private $bootstrapFile;

    /**
     * Migrations Manager class instance.
     *
     * @var Manager
     **/
    private $manager;

    /**
     * Validation that this instance has all the required properties configured
     *
     * @return void
     * @throws MigrationException
     */
    public function validate()
    {
        if ( ! $this->migrationsNamespace) {
            throw MigrationException::migrationsNamespaceRequired();
        }
        if ( ! $this->migrationsDirectory) {
            throw MigrationException::migrationsDirectoryRequired();
        }
        if ( ! $this->bootstrapFile) {
            throw MigrationException::migrationsBootstrapFileRequired();
        }
    }

    /**
     * Gets the Name of this set of migrations.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the Name of this set of migrations.
     *
     * @param string $name the name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Gets the The path to a directory where new migration classes will be written.
     *
     * @return string
     */
    public function getMigrationsDirectory()
    {
        return $this->migrationsDirectory;
    }

    /**
     * Sets the The path to a directory where new migration classes will be written.
     *
     * @param string $migrationsDirectory the migrationsDirectory
     */
    public function setMigrationsDirectory($migrationsDirectory)
    {
        $this->migrationsDirectory = $migrationsDirectory;
    }

    /**
     * Gets the Namespace the migration classes live in.
     *
     * @return string
     */
    public function getMigrationsNamespace()
    {
        return $this->migrationsNamespace;
    }

    /**
     * Sets the Namespace the migration classes live in.
     *
     * @param string $migrationsNamespace the migrationsNamespace
     */
    public function setMigrationsNamespace($migrationsNamespace)
    {
        $this->migrationsNamespace = $migrationsNamespace;
    }

    /**
     * Gets the Bootstrap file to load your migrations..
     *
     * @return string
     */
    public function getBootstrapFile()
    {
        return $this->bootstrapFile;
    }

    /**
     * Sets the Bootstrap file to load your migrations..
     *
     * @param string $bootstrapFile the bootstrapFile
     */
    public function setBootstrapFile($bootstrapFile)
    {
        if (file_exists($path = getcwd() . '/' . $bootstrapFile)) {
            $bootstrapFile = $path;
        }
        
        $this->bootstrapFile = $bootstrapFile;
    }

    public function getManager()
    {
        if ($this->manager === null) {
            $this->manager = include $this->bootstrapFile;
        }

        return $this->manager;
    }
}
