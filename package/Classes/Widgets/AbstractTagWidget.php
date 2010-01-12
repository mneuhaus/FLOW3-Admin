<?php
 
namespace F3\Admin\Widgets;

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
 * @api
 * @scope prototype
 */
class AbstractTagWidget extends \F3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper {
	/**
	 * Match validator names and options
	 * @var string
	 */
	const PATTERN_MATCH_VALIDATORS = '/
			(?:^|,\s*)
			(?P<validatorName>[a-z0-9\\\\]+)
			\s*
			(?:\(
				(?P<validatorOptions>(?:\s*[a-z0-9]+\s*=\s*(?:
					"(?:\\\\"|[^"])*"
					|\'(?:\\\\\'|[^\'])*\'
					|(?:\s|[^,"\']*)
				)(?:\s|,)*)*)
			\))?
		/ixS';

	/**
	 * Match validator options (to parse actual options)
	 * @var string
	 */
	const PATTERN_MATCH_VALIDATOROPTIONS = '/
			\s*
			(?P<optionName>[a-z0-9]+)
			\s*=\s*
			(?P<optionValue>
				"(?:\\\\"|[^"])*"
				|\'(?:\\\\\'|[^\'])*\'
				|(?:\s|[^,"\']*)
			)
		/ixS';

    /**
	 * Parses the validator options given in @validate annotations.
	 *
	 * @return array
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function parseValidatorAnnotation($validateValue) {
		$matches = array();
		if ($validateValue[0] === '$') {
			$parts = explode(' ', $validateValue, 2);
			$validatorConfiguration = array('argumentName' => ltrim($parts[0], '$'), 'validators' => array());
			preg_match_all(self::PATTERN_MATCH_VALIDATORS, $parts[1], $matches, PREG_SET_ORDER);
		} else {
			$validatorConfiguration = array('validators' => array());
			preg_match_all(self::PATTERN_MATCH_VALIDATORS, $validateValue, $matches, PREG_SET_ORDER);
		}

		foreach ($matches as $match) {
			$validatorOptions = array();
			if (isset($match['validatorOptions'])) {
				$validatorOptions = $this->parseValidatorOptions($match['validatorOptions']);
			}
			$validatorConfiguration['validators'][] = array('validatorName' => $match['validatorName'], 'validatorOptions' => $validatorOptions);
		}

		return $validatorConfiguration;
	}

	/**
	 * Parses $rawValidatorOptions not containing quoted option values.
	 * $rawValidatorOptions will be an empty string afterwards (pass by ref!).
	 *
	 * @param string &$rawValidatorOptions
	 * @return array An array of optionName/optionValue pairs
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function parseValidatorOptions($rawValidatorOptions) {
		$validatorOptions = array();
		$parsedValidatorOptions = array();
		preg_match_all(self::PATTERN_MATCH_VALIDATOROPTIONS, $rawValidatorOptions, $validatorOptions, PREG_SET_ORDER);
		foreach ($validatorOptions as $validatorOption) {
			$parsedValidatorOptions[trim($validatorOption['optionName'])] = trim($validatorOption['optionValue']);
		}
		array_walk($parsedValidatorOptions, array($this, 'unquoteString'));
		return $parsedValidatorOptions;
	}

	/**
	 * Removes escapings from a given argument string.
	 *
	 * This method is meant as a helper for regular expression results.
	 *
	 * @param string &$quotedValue Value to unquote
	 * @return string Unquoted value
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function unquoteString(&$quotedValue) {
		switch ($quotedValue[0]) {
			case '"':
				$quotedValue = str_replace('\"', '"', trim($quotedValue, '"'));
			break;
			case '\'':
				$quotedValue = str_replace('\\\'', '\'', trim($quotedValue, '\''));
			break;
		}
		$quotedValue = str_replace('\\\\', '\\', $quotedValue);
	}
}

?>
