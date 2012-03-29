<?php
namespace Admin;

use \TYPO3\FLOW3\Package\Package as BasePackage;
use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * Package base class of the Admin package.
 *
 * @FLOW3\Scope("singleton")
 */
class Package extends BasePackage {
	/**
	 * Invokes custom PHP code directly after the package manager has been initialized.
	 *
	 * @param \TYPO3\FLOW3\Core\Bootstrap $bootstrap The current bootstrap
	 * @return void
	 */
	public function boot(\TYPO3\FLOW3\Core\Bootstrap $bootstrap) {
		#set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/../Resources/PHP/');
		require(__DIR__ . '/../Resources/PHP/LessPHP/lessc.inc.php');
	}
}
?>