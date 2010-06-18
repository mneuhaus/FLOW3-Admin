<?php
declare(ENCODING = 'utf-8');
namespace F3\Admin\Security;

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

/**
 * An account model
 *
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 * @entity
 * @autoadmin
 * @group Settings
 */
class Role extends \F3\Admin\Domain\Model{
    /**
     * @var string
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
    protected $name;

    /**
	 * @var \SplObjectStorage<\F3\Admin\Security\Policy>
     * @optionsProvider \F3\Admin\OptionsProvider\PolicyOptionsProvider
     * @ignore list
     * @label Actions to Grant
     *
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
    protected $grant;

    /**
	 * @var \SplObjectStorage<\F3\Admin\Security\Policy>
     * @optionsProvider \F3\Admin\OptionsProvider\PolicyOptionsProvider
     * @ignore list
     * @label Actions to Deny
     *
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
    #protected $deny;

    public function __toString(){
        return $this->name;
    }
}
?>