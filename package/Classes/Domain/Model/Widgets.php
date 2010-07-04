<?php

namespace F3\Admin\Domain\Model;

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

/**
 * A Adress
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @scope prototype
 * @entity
 * @autoadmin
 * @set Default Types(string,integer,float,boolean,datetime,resource,tag,tags)
 * @set Textinput(textarea,autoexpand,fullrte,markdown)
 * @set Inline(widget)
 */
class Widgets extends \F3\Admin\Domain\Model{
	/**
	 * @var string
	 * @identity
	 */
	protected $string;
	
	/**
	 * @var integer
	 */
	protected $integer;
	
	/**
	 * @var float
	 */
	protected $float;
	
	/**
	 * @var boolean
	 */
	protected $boolean;
	
	/**
	 * @var \DateTime
	 */
	protected $datetime;
	
	/**
	 * @var \F3\FLOW3\Resource\Resource
	 * validate NotEmpty
	 */
	protected $resource;
	
	/**
	 * @var \F3\Admin\Domain\Model\Tag
	 */
	protected $tag;
	
	/**
	 * @var \SplObjectStorage<\F3\Admin\Domain\Model\Tag>
	 */
	protected $tags;
	
	
	
	/**
	 * @var string
	 * @widget Textarea
	 */
	protected $textarea;
	
	/**
	 * @var string
	 * @widget Textarea
	 * @class f-autoexpand
	 */
	protected $autoexpand;
	
	/**
	 * @var string
	 * @widget RichTextEditor
	 */
	protected $fullrte;
	
	/**
	 * @var string
	 * @widget textarea
	 * @class f-markdown
	 */
#	protected $markdown;
	
	/**
	 * @var \F3\Admin\Domain\Model\Widgets
	 * @inline
	 */
#	protected $widget;

    public function __construct(){
		$this->tags = new \SplObjectStorage();
    }
}

?>