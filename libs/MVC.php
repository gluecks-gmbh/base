<?php
/**
 * Main file for MVC
 *
 * @package        BASE
 * @subpackage     MVC
 * @author         Frederik Glücks <frederik@gluecks-gmbh.de>
 * @license        lgpl-3.0
 *
 */

namespace BASE;

use BASE\MVC\Controller;
use BASE\MVC\Links;
use BASE\MVC\Local;
use BASE\MVC\Routes;
use BASE\MVC\Uri;

/**
 * Class MVC
 *
 * @package        BASE
 * @author         Frederik Glücks <frederik@gluecks-gmbh.de>
 * @license        lgpl-3.0
 */
class MVC
{
	/**
	 * Starts the MVC
	 *
	 * @return string
	 */
	public static function run(): string
	{
		Config::loadXml();

		$uri = Uri::get();
		$code = Local::getCode();

		if ($code === null) {
			header("HTTP/1.0 404 Not Found");
			return '404 Not found - invalid language-country code';
		} else {
			$Routes = new Routes($code);
			Links::setRoutes($Routes->getRoutes());

			$controller = $Routes->getControllerByUri($uri);

			if ($controller === null) {
				header("HTTP/1.0 404 Not Found");
				return "404 Not found";
			} else {
				$Controller = new Controller();
				$Controller->load($controller['controller'], $controller['regExpMatches'], $code);

				return "";
			}
		}
	}
}