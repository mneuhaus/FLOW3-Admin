<?php
declare(ENCODING = 'utf-8');
namespace Admin\Security;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Doctrine\ORM\Mapping as ORM;
use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * An role model
 *
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @FLOW3\Scope("prototype")
 * @FLOW3\Entity
 * @Admin\Annotations\Active
 * @Admin\Annotations\Group("System")
 */
class Role extends \Admin\Core\Domain\Magic{
    /**
     * @var string
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
    protected $name;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection<\Admin\Security\Policy>
	 * @ORM\ManyToMany(inversedBy="roles")
	 * @Admin\Annotations\OptionsProvider \Admin\OptionsProvider\PolicyOptionsProvider
	 * @Admin\Annotations\Ignore("list")
	 * @Admin\Annotations\Label("Actions to Grant")
	 *
	 * @author Marc Neuhaus <mneuhaus@famelo.com>
	 * */
	protected $grant;

    /**
	 * @var \SplObjectStorage<\Admin\Security\Policy>
     * @Admin\Annotations\OptionsProvider \Admin\OptionsProvider\PolicyOptionsProvider
     * @Admin\Annotations\Ignore("list")
     * @Admin\Annotations\Label("Actions to Deny")
     *
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
    #protected $deny;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection<\Admin\Security\User>
	 * @ORM\ManyToMany(inversedBy="roles")
	 * @Admin\Annotations\Ignore
	 *
	 * @author Marc Neuhaus <mneuhaus@famelo.com>
	 * */
	protected $users;

    public function __toString(){
        return $this->name;
    }
}
?>