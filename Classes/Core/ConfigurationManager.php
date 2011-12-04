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
	 * @var TYPO3\FLOW3\Cache\CacheManager
	 * @FLOW3\Inject
	 */
	protected $cacheManager;

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
	 * @var \TYPO3\FLOW3\Package\PackageManagerInterface
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $packageManager;

	/**
	 * @var \TYPO3\FLOW3\Reflection\ReflectionService
	 * @api
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $reflectionService;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @var array
	 */
	protected $runtimeCache = array();

	public function __construct(\Admin\Core\Helper $helper){
		$this->settings = $helper->getSettings();
	}

	public function getClassConfiguration($class){
		$implementations = class_implements("\\" . ltrim($class, "\\"));
		if(in_array("Doctrine\ORM\Proxy\Proxy", $implementations))
			$class = get_parent_class("\\" . ltrim($class, "\\"));

		$this->class = $class;

		if(isset($this->settings["ConfigurationProvider"]) && !isset($this->runtimeCache[$class])){
			$configuration = array();
			$configurationProviders = $this->settings["ConfigurationProvider"];
			foreach($configurationProviders as $configurationProviderClass){
				$configurationProvider = $this->objectManager->get($configurationProviderClass);
				$configurationProvider->setSettings($this->settings);
				$configuration = $this->merge($configuration, $configurationProvider->get($class));

			}
			$this->runtimeCache[$class] = $configuration;
		}

		return $this->runtimeCache[$class];
	}

	public function merge($configuration, $override){
		foreach ($override as $annotation => $objects) {
			if($annotation == "properties"){

				if(!isset($configuration[$annotation]))
					$configuration[$annotation] = array();
				$configuration[$annotation] = $this->merge($configuration[$annotation], $objects);

			}else{

				foreach ($objects as $key => $object) {
					try{if(isset($object->multiple) && $object->multiple){
							$configuration[$annotation][] = $object;
						}else{
							$configuration[$annotation][$key] = $object;
						}
					}catch(\TYPO3\FLOW3\Error\Exception $e){}
				}

			}
		}
		return $configuration;
	}

	/**
	 * returns classes that are taged with all of the specified tags
	 *
	 * @param string $tags
	 * @return void
	 * @author Marc Neuhaus
	 */
	public function getClassesAnnotatedWith($tags){
		$cache = $this->cacheManager->getCache('Admin_ImplementationCache');
		$identifier = "ClassesTaggedWith-".implode("_",$tags);

		if(!$cache->has($identifier)){
			$classes = array();

			$activePackages = $this->packageManager->getActivePackages();
			foreach($activePackages as $packageName => $package) {
				if(substr($packageName, 0, 8) === "Doctrine") continue;
				foreach($package->getClassFiles() as $class => $file) {
					$annotations = $this->getClassConfiguration($class);

					$tagged = true;
					foreach($tags as $tag){
						if(!isset($annotations[$tag])) $tagged = false;
					}

					if($tagged)
						$classes[$class] = $packageName;
				}
			}

			$cache->set($identifier,$classes);
		}elseif(isset($this->runtimeCache[$identifier])){
			$classes = $this->runtimeCache[$identifier];
		}else{
			$this->runtimeCache[$identifier] = $classes = $cache->get($identifier);
		}
		return $classes;
	}

	public function setConfigurationProviders($configurationProviders){
		$this->configurationProviders = $configurationProviders;
	}

	public function setSettings($settings){
		$this->settings = $settings;
	}
}

?>
