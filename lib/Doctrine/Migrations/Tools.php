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
 * Tools
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       3.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class Tools
{
    public static function getExecutionState($state)
    {
        switch ($state) {
            case AbstractMigration::STATE_PRE:
                return 'Pre-Checks';
            case AbstractMigration::STATE_POST:
                return 'Post-Checks';
            case AbstractMigration::STATE_EXEC:
                return 'Execution';
            default:
                return 'No State';
        }
    }

    /**
     * Returns a timestamp version as a formatted date
     *
     * @param string $version
     *
     * @return string The formatted version
     */
    public static function formatVersion($version)
    {
        if (strlen($version) === 14) {
            return sprintf('%s-%s-%s %s:%s:%s',
                substr($version, 0, 4),
                substr($version, 4, 2),
                substr($version, 6, 2),
                substr($version, 8, 2),
                substr($version, 10, 2),
                substr($version, 12, 2)
            );
        } else {
            return $version;
        }
    }

    public static function getDirectoryRelativeToFile($file, $input)
    {
        $path = realpath(dirname($file) . '/' . $input);
        if ($path !== false) {
            $directory = $path;
        } else {
            $directory = $input;
        }

        return $directory;
    }
}
