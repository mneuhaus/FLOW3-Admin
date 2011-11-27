<?php
namespace Admin\Annotations;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * @Annotation
 */
final class Representation {
	/**
	 * @param string $value
	 */
	public function __construct(array $values = array()) {
		foreach ($values as $key => $value) {
			$this->$key = $value;
		}
	}
	
	public function getDatetimeFormatJs(){
	    $mapping = array(
		    "Y" => "yyyy",
		    "m" => "MM",
		    "d" => "dd",
		    "\T"=> "T",
		    "H" => "HH",
		    "i" => "mm",
		    "s" => "ss",
		    "P" => "TZD"
	    );
	    return str_replace(array_keys($mapping), array_values($mapping), $this->datetimeFormat);
	}
}

?>