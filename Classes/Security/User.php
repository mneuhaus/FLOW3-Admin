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
 * An user model
 *
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @FLOW3\Scope("prototype")
 * @FLOW3\Entity
 * @Admin\Annotations\Active
 * @Admin\Annotations\Group("System")
 */
class User extends \Admin\Core\Domain\Magic{
	/**
	 * @var string
	 * @FLOW3\Identity
	 * @FLOW3\Validate(type="NotEmpty")
	 * @FLOW3\Validate(type="StringLength", options={ "minimum"=1, "maximum"=255 })
     * @Admin\Annotations\Label("Username")
	 */
	protected $accountIdentifier;
	
	/**
	 * @var string
     * @Admin\Annotations\Label("E-Mail")
	 */
	protected $email;
	
	/**
	 * @var string
	 * @FLOW3\Identity
	 * @FLOW3\Validate(type="NotEmpty")
     * @Admin\Annotations\Ignore
	 */
	protected $authenticationProviderName = "AdminProvider";

	/**
	 * @var string
     * @Admin\Annotations\Widget Password
     * @Admin\Annotations\Label Password
     * @Admin\Annotations\Ignore("list,view")
	 */
	protected $credentialsSource;

	/**
	 * @var boolean
	 */
	protected $admin = false;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection<\Admin\Security\Role>
	 * @ORM\ManyToMany(inversedBy="users")
	 * @Admin\Annotations\Ignore("list")
	 *
	 * @author Marc Neuhaus <mneuhaus@famelo.com>
	 * */
	protected $roles;
	
	/**
	 * @var \Doctrine\Common\Collections\Collection<\Admin\Domain\Model\Log>
	 * @ORM\OneToMany(mappedBy="user")
     * @Admin\Annotations\Ignore("list,view")
	 */
	protected $logs;

    public function __construct(){
    }

	public function getArguments(){
		return array(
			"id" => $this->getIdentity(),
			"being" => "Admin\Security\User",
			"adapter" => \Admin\Core\API::get("adapter")
		);
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

	public function __toString(){
		return $this->accountIdentifier;
	}
}
?>