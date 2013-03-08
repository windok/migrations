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

use Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Doctrine\Migrations\MigrationException;

/**
 * Command for manually adding and deleting migration versions from the version table.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   3.0
 * @author  Jonathan Wage <jonwage@gmail.com>
 */
class VersionCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('migrations:version')
            ->setDescription('Log a version without executing the migration.')
            ->addArgument('version', InputArgument::REQUIRED, 'The version to log up or down.', null)
            ->addOption('up', null, InputOption::VALUE_NONE, 'Record a log for the version going up.')
            ->addOption('down', null, InputOption::VALUE_NONE, 'Record a log for the version going down.')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command allows you to manually log a migration version up or down without executing the migration:

    <info>%command.full_name% YYYYMMDDHHMMSS --up</info>

If you want to mark a migration down can use the <comment>--down</comment> option:

    <info>%command.full_name% YYYYMMDDHHMMSS --down</info>
EOT
        );

        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $configuration = $this->getMigrationConfiguration($input);
        $manager = $this->getMigrationManager($input);

        if ($input->getOption('up') === false && $input->getOption('down') === false) {
            throw new \InvalidArgumentException('You must specify whether you want to --up or --down the specified version.');
        }

        $version = $input->getArgument('version');
        $markMigrated = $input->getOption('up') ? true : false;

        if ( ! $manager->hasVersion($version)) {
            throw MigrationException::unknownMigrationVersion($version);
        }

        if ($markMigrated && $manager->isVersionMigrated($version)) {
            throw new \InvalidArgumentException(sprintf('The version "%s" already exists in the version logs.', $version));
        }

        if ( ! $markMigrated && ! $manager->isVersionMigrated($version)) {
            throw new \InvalidArgumentException(sprintf('The version "%s" does not exists in the version logs.', $version));
        }

        if ($markMigrated) {
            $manager->getLogger()->logUp($version, true);
        } else {
            $manager->getLogger()->logDown($version, true);
        }
    }
}
