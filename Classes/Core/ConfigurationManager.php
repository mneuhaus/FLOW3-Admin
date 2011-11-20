<?php

namespace Admin\Core;

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
 * 
 * @version $Id: ForViewHelper.php 3346 2009-10-22 17:26:10Z k-fish $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @FLOW3\Scope("singleton")
 */
class ConfigurationManager{
	/**
	 * @var array
	 */
	protected $configurationProviders;
	
	/**
	 * @var \TYPO3\FLOW3\Object\ObjectManagerInterface
	 * @api
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $objectManager;
	
	/**
	 * @var array
	 */
	protected $settings;
	
	public function __construct(\Admin\Core\Helper $helper){
		$this->settings = $helper->getSettings();
	}
	
	public function getClassConfiguration($class){
		$configuration = array();
		
		if(isset($this->settings["ConfigurationProvider"])){
			
			$configurationProviders = $this->settings["ConfigurationProvider"];
			foreach($configurationProviders as $configurationProviderClass){
				$configurationProvider = $this->objectManager->get($configurationProviderClass);
				$configurationProvider->setSettings($this->settings);
				$configuration = array_merge_recursive($configuration,$configurationProvider->get($class));
			}
		}
		
#		$configuration = $this->postProcessConfiguration($configuration);
		
		return $configuration;
	}
	
	public function getPropertyConfiguration($property){
	}
	
	public function setConfigurationProviders($configurationProviders){
		$this->configurationProviders = $configurationProviders;
	}
	
	public function setSettings($settings){
		$this->settings = $settings;
	}
}

?>