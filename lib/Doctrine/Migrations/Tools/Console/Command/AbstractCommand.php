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

namespace Doctrine\Migrations\Tools\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOptio;
use Doctrine\Migrations\MigrationException;
use Doctrine\Migrations\OutputWriter;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Configuration\YamlConfiguration;
use Doctrine\Migrations\Configuration\XmlConfiguration;

/**
 * CLI Command for adding and deleting migration versions from the version table.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   3.0
 * @author  Jonathan Wage <jonwage@gmail.com>
 */
abstract class AbstractCommand extends Command
{
    /**
     * @var Configuration
     */
    private $configuration;

    protected function configure()
    {
        $this->addOption('configuration', null, InputOption::VALUE_OPTIONAL, 'The path to a migrations configuration file.');
    }

    protected function outputHeader(Configuration $configuration, OutputInterface $output)
    {
        $name = $configuration->getName();
        $name = $name ? $name : 'Doctrine Database Migrations';
        $name = str_repeat(' ', 20) . $name . str_repeat(' ', 20);
        $output->writeln('<question>' . str_repeat(' ', strlen($name)) . '</question>');
        $output->writeln('<question>' . $name . '</question>');
        $output->writeln('<question>' . str_repeat(' ', strlen($name)) . '</question>');
        $output->writeln('');
    }

    public function setMigrationConfiguration(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return Configuration
     */
    protected function getMigrationConfiguration(InputInterface $input)
    {
        if ( ! $this->configuration) {
            if ($file = $input->getOption('configuration')) {
                if (file_exists($path = getcwd() . '/' . $file)) {
                    $file = $path;
                }

                $info = pathinfo($file);
                $class = $info['extension'] === 'xml' ? 'Doctrine\Migrations\Configuration\XmlConfiguration' : 'Doctrine\Migrations\Configuration\YamlConfiguration';
                $configuration = new $class();
                $configuration->load($input->getOption('configuration'));
            } elseif (file_exists('migrations.xml')) {
                $configuration = new XmlConfiguration();
                $configuration->load('migrations.xml');
            } elseif (file_exists('migrations.yml')) {
                $configuration = new YamlConfiguration();
                $configuration->load('migrations.yml');
            } else {
                $configuration = new Configuration();
            }
            $this->configuration = $configuration;
        }

        return $this->configuration;
    }

    protected function getMigrationManager(InputInterface $input)
    {
        return $this->getMigrationConfiguration($input)->getManager();
    }
}
