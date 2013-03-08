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
    Doctrine\Migrations\Tools;

/**
 * Command to view the status of a set of migrations.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   3.0
 * @author  Jonathan Wage <jonwage@gmail.com>
 */
class StatusCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('migrations:status')
            ->setDescription('View the status of a set of migrations.')
            ->addOption('show-versions', null, InputOption::VALUE_NONE, 'This will display a list of all available migrations and their status')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command outputs the status of a set of migrations:

    <info>%command.full_name%</info>

You can output a list of all available migrations and their status with <comment>--show-versions</comment>:

    <info>%command.full_name% --show-versions</info>
EOT
        );

        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $configuration = $this->getMigrationConfiguration($input);
        $manager = $this->getMigrationManager($input);

        $currentVersion = $manager->getCurrentVersion();
        if ($currentVersion) {
            $currentVersionFormatted = Tools::formatVersion($currentVersion);
        } else {
            $currentVersionFormatted = 0;
        }
        $newestVersion = $manager->getNewestVersion();
        if ($newestVersion) {
            $newestVersionFormatted = Tools::formatVersion($newestVersion);
        } else {
            $newestVersionFormatted = 0;
        }

        $executedMigrations = $manager->getExecutedMigrations();
        $availableMigrations = $manager->getMigrations();
        $executedUnavailableMigrations = array_diff($executedMigrations, iterator_to_array($availableMigrations));
        $numExecutedUnavailableMigrations = count($executedUnavailableMigrations);
        $newMigrations = count($availableMigrations) - count($executedMigrations);

        $output->writeln("\n <info>==</info> Configuration\n");

        $info = array(
            'Name'                              => $configuration->getName() ? $configuration->getName() : 'Doctrine Database Migrations',
            'Configuration Source'              => $configuration instanceof \Doctrine\DBAL\Migrations\Configuration\AbstractFileConfiguration ? $configuration->getFile() : 'manually configured',
            'Migrations Namespace'              => $configuration->getMigrationsNamespace(),
            'Migrations Directory'              => $configuration->getMigrationsDirectory(),
            'Migrations Bootstrap File'         => $configuration->getBootstrapFile(),
            'Current Version'                   => $currentVersionFormatted,
            'Latest Version'                    => $newestVersionFormatted,
            'Executed Migrations'               => count($executedMigrations),
            'Executed Unavailable Migrations'   => $numExecutedUnavailableMigrations > 0 ? '<error>'.$numExecutedUnavailableMigrations.'</error>' : 0,
            'Available Migrations'              => count($availableMigrations),
            'New Migrations'                    => $newMigrations > 0 ? '<question>' . $newMigrations . '</question>' : 0
        );
        foreach ($info as $name => $value) {
            $output->writeln('    <comment>>></comment> ' . $name . ': ' . str_repeat(' ', 50 - strlen($name)) . $value);
        }

        $showVersions = $input->getOption('show-versions') ? true : false;
        if ($showVersions === true) {
            if ($migrations = $manager->getMigrations()) {
                $output->writeln("\n <info>==</info> Available Migration Versions\n");
                foreach ($migrations as $migration) {
                    $isMigrated = in_array($migration, $executedMigrations);
                    $status = $isMigrated ? '<info>migrated</info>' : '<error>not migrated</error>';
                    $output->writeln('    <comment>>></comment> ' . Tools::formatVersion($version->getVersion()) . ' (<comment>' . $version->getVersion() . '</comment>)' . str_repeat(' ', 30 - strlen($name)) . $status);
                }
            }

            if ($executedUnavailableMigrations) {
                $output->writeln("\n <info>==</info> Previously Executed Unavailable Migration Versions\n");
                foreach ($executedUnavailableMigrations as $executedUnavailableMigration) {
                    $output->writeln('    <comment>>></comment> ' . Tools::formatVersion($executedUnavailableMigration) . ' (<comment>' . $executedUnavailableMigration . '</comment>)');
                }
            }
        }
    }
}
