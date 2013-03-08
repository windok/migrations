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

use Doctrine\Migrations\Configuration\Configuration,
    Doctrine\Migrations\Version;

/**
 * Abstract class for migration classes to extend from.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       3.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
abstract class AbstractMigration
{
    const STATE_NONE = 0;
    const STATE_PRE  = 1;
    const STATE_EXEC = 2;
    const STATE_POST = 3;

    protected $notifier;
    private $state = self::STATE_NONE;

    public function __construct(Notifier $nofitier)
    {
        $this->notifier = $nofitier;
    }

    public function getNotifier()
    {
        return $this->notifier;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState($state)
    {
        $this->state = $state;
    }

    abstract public function getVersion();
    abstract public function up();
    abstract public function down();

    public function execute()
    {
    }

    public function preExecute()
    {
    }

    public function postExecute()
    {
    }

    public function preUp()
    {
    }

    public function postUp()
    {
    }

    public function preDown()
    {
    }

    public function postDown()
    {
    }

    public function __toString()
    {
        return (string) $this->getVersion();
    }
}
