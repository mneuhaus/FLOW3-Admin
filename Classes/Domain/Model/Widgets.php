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
use Admin\Annotations as Admin;

/**
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @FLOW3\Scope("prototype")
 * @FLOW3\Entity
 * @Admin\Active
 * @Admin\Group("Testcases")
 * @Admin\Set(title="Default Types", properties="string,integer,float,boolean,datetime,resource,tag,tags")
 * @Admin\Set(title="Textinput", properties="textarea,autoexpand,fullrte,markdown")
 * @Admin\Set(title="Inline", properties="widget")
 */
class Widgets extends \Admin\Core\Domain\Magic{
	/**
	 * @var string
	 * @FLOW3\Identity
	 */
	protected $string;
	
	/**
	 * @var integer
	 * @ORM\Column(type="integer", nullable=true)
	 */
#	protected $integer;
	
	/**
	 * @var float
	 */
#	protected $float;
	
	/**
	 * @var boolean
	 */
	protected $boolean;
	
	/**
	 * @var \DateTime
	 */
	protected $datetime;
	
	/**
	 * @var \TYPO3\FLOW3\Resource\Resource
	 * @ORM\OneToOne
	 * @FLOW3\Validate(type="NotEmpty")
	 */
	protected $resource;
	
	/**
	 * @var \Admin\Domain\Model\Tag
	 * @ORM\ManyToOne(inversedBy="comments")
	 */
	protected $tag;
	
	/**
	 * @var \Doctrine\Common\Collections\Collection<\Admin\Domain\Model\Tag>
	 * @ORM\ManyToMany(inversedBy="widgets_manytomany")
	 * @Admin\Ignore("list")
	 */
	protected $tags;
	
	
	/**
	 * @var string
	 * @Admin\Widget("Textarea")
	 */
	protected $textarea;
	
	/**
	 * @var string
	 * @Admin\Editor("RichText")
	 */
	protected $fullrte;
	
	/**
	 * @var string
	 * @Admin\Editor("Markdown")
	 */
	protected $markdown;
	
	/**
	 * @var \Admin\Domain\Model\Info
	 * @ORM\ManyToOne(inversedBy="comments")
	 * @Admin\Inline
	 */
	protected $info;

    public function __construct(){
#		$this->tags = new \SplObjectStorage();
    }
}

?>