<?php

namespace F3\Admin\Core;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
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
 * @version $Id: ForViewHelper.php 3346 2009-10-22 17:26:10Z k-fish $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @scope prototype
 */
class Value{
    /**
	 * @var \F3\FLOW3\Object\ObjectManagerInterface
	 * @api
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @var \F3\FLOW3\Reflection\ReflectionService
	 * @api
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @inject
	 */
	protected $reflectionService;

	/**
	 * @var \F3\Admin\Core\Converter
	 * @api
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @inject
	 */
	protected $converter;

    protected $parent;
    protected $adapter;

    public function  __construct($parent, $adapter) {
        $this->parent = $parent;
        $this->adapter = $adapter;
    }

    public function  __toString() {
        $value = $this->parent->getValue();
        return $this->converter->toString($value, $this->parent->getConf());
    }

    public function getValue() {
        return $this->parent->getValue();
    }

    public function getIds(){
        $value = $this->getValue();
        $ids = array();
        if( \F3\Admin\Core\Helper::isIteratable($value) ){
            foreach($value as $object){
                $ids[] = $this->adapter->getId($object);
            }
        }else if (is_object($value)){
            $ids[] = $this->adapter->getId($value);
        }
        #\F3\dump($ids,$this->parent->getName());
        return $ids;
    }
}

?>