<?php
declare(ENCODING = 'utf-8');
namespace F3\Admin\Converter;

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
 * An object converter for Resource objects
 *
 * @version $Id: ResourceObjectConverter.php 4031 2010-03-30 09:55:23Z robert $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class DateTimeObjectConverter implements \F3\FLOW3\Property\ObjectConverterInterface {

	/**
	 * @var F3\FLOW3\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * Returns a list of fully qualified class names of those classes which are supported
	 * by this property editor.
	 *
	 * @return array<string>
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getSupportedTypes() {
		return array('DateTime','datetime',"Datetime");
	}

	/**
	 * @return object An object or an instance of F3\FLOW3\Error\Error if the input format is not supported or could not be converted for other reasons
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function convertFrom($string, $conf = null) {
        if(is_array($string))
            $string = current($string);
        return new \DateTime($string);
	}

    public function toString($object, $conf = null){
        $format = "d.m.Y";
        
        if(isset($conf["format"]))
            $format = $conf["format"];
        
        return $object->format($format);
    }
}

?>