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
class User extends \F3\Admin\Domain\Model{
	/**
	 * @var string
	 * @identity
	 * @validate NotEmpty, StringLength(minimum = 1, maximum = 255)
     * @label Username
	 */
	protected $accountIdentifier;

	/**
	 * @var string
	 * @identity
	 * @validate NotEmpty
     * @ignore
	 */
	protected $authenticationProviderName = "PHPCRProvider";

	/**
	 * @var string
     * @widget Password
     * @label Password
     * @ignore list,view
	 */
	protected $credentialsSource;

	/**
	 * @var boolean
	 */
	protected $admin = false;


	/**
	 * @var \SplObjectStorage<\F3\Admin\Security\Role>
	 */
	protected $roles;

    public function __construct(){
        $this->roles = new \SplObjectStorage();
        #$this->roles[] = \F3\Admin\Register::get("objectManager")->create('F3\FLOW3\Security\Policy\Role', 'Administrator');
    }

    /**
	 *
	 * @param string $credentialsSource
	 * @return void
	 */
	public function setCredentialsSource($credentialsSource) {
        $salt = sha1(rand(0,4).time());
        $this->credentialsSource = md5(md5($credentialsSource) . $salt) . ',' . $salt;
	}

    public function isAdmin(){
        return $this->admin;
    }
}
?>