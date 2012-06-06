<?php
namespace Admin\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "Contacts".                   *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License as published by the Free   *
 * Software Foundation, either version 3 of the License, or (at your      *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        *
 * You should have received a copy of the GNU General Public License      *
 * along with the script.                                                 *
 * If not, see http://www.gnu.org/licenses/gpl.html                       *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Doctrine\ORM\Mapping as ORM;
use TYPO3\FLOW3\Annotations as FLOW3;

/**
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @FLOW3\Entity
 */
class Log extends \Admin\Core\Domain\Magic{
	/**
	 * @var string
	 * @Admin\Annotations\Ignore
	 */
	protected $description = "";
	
	/**
	 * @var string
	 * @ORM\Column(length=20)
	 */
	protected $datetime;
	
	/**
	 * @var string
	 * @Admin\Annotations\Ignore list
	 */
	protected $identity;
	
	/**
	 * @var string
	 */
	protected $being;
	
	/**
	 * @var string
	 */
	protected $action;
	
	/**
	 * @var string
	 */
	protected $adapter;
	
	/**
	 * @var \Admin\Security\User
	 * @ORM\ManyToOne(inversedBy="logs")
	 */
	protected $user;
	
	public function __construct(){
		$this->datetime = time();
	}
}

?>