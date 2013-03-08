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
 * Notifier
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       3.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class Notifier
{
    private $outputWriter;

    public function __construct(OutputWriter $outputWriter)
    {
        $this->outputWriter = $outputWriter;
    }

    public function write($message)
    {
        $this->outputWriter->write($message);
    }

    public function throwIrreversibleMigrationException($message = null)
    {
        if ($message === null) {
            $message = 'This migration is irreversible and cannot be reverted.';
        }

        throw new IrreversibleMigrationException($message);
    }

    /**
     * Print a warning message if the condition evalutes to TRUE.
     *
     * @param boolean $condition
     * @param string  $message
     */
    public function warnIf($condition, $message = '')
    {
        $message = (strlen($message)) ? $message : 'Unknown Reason';

        if ($condition === true) {
            $this->outputWriter->write('    <warning>' . $message . '</warning>');
        }
    }

    /**
     * Abort the migration if the condition evalutes to TRUE.
     *
     * @param boolean $condition
     * @param string  $message
     *
     * @throws AbortMigrationException
     */
    public function abortIf($condition, $message = '')
    {
        $message = (strlen($message)) ? $message : 'Unknown Reason';

        if ($condition === true) {
            throw new AbortMigrationException($message);
        }
    }

    /**
     * Skip this migration (but not the next ones) if condition evalutes to TRUE.
     *
     * @param boolean $condition
     * @param string  $message
     *
     * @throws SkipMigrationException
     */
    public function skipIf($condition, $message = '')
    {
        $message = (strlen($message)) ? $message : 'Unknown Reason';

        if ($condition === true) {
            throw new SkipMigrationException($message);
        }
    }
}
