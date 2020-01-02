<?php
/**
 * Class to parse the routes and returns the controller to an used uri.
 *
 * @package    BASE
 * @subpackage MVC
 * @author     Frederik Glücks <frederik@gluecks-gmbh.de>
 *
 */

namespace BASE\MVC;

use BASE\Config;
use BASE\Helper\RegExp;
use RuntimeException;

/**
 * Class to parse the routes and returns the controller to an used uri.
 *
 * @package BASE\MVC
 */
class Routes
{
	/**
	 * Array to store the uri-controller structure
	 *
	 * @var array
	 */
	private $uris = [];

	/**
	 * Routes constructor.
	 *
	 * @param string $code
	 */
	public function __construct(string $code)
	{
		$this->load($code);
	}

	/**
	 *
	 * Loads the routes.xml
	 *
	 * @param string $code
	 *
	 * @return bool
	 */
	private function load(string $code)
	{
		if (!preg_match(RegExp::getLocalCode(), $code)) {
			throw new RuntimeException("invalid language-country code", E_ERROR);
		} else {
			$routesFile = Config::getAppDir() . "config/routes.xml";

			if (!file_exists($routesFile)) {
				throw new RuntimeException("Missing routes.xml in <app-dir>/config folder.", E_ERROR);
			} else {
				$routesXML = simplexml_load_file($routesFile);

				$this->uris = $this->parseRoutes($routesXML, $code, [], "");
			}

			return true;
		}
	}

	/**
	 *
	 * Parses the routes XML object and creates an uri-controller array
	 *
	 * @param object $routes
	 * @param string $code
	 * @param array  $uris
	 * @param string $parentUri
	 *
	 * @return array
	 */
	private function parseRoutes(object $routes, string $code, array $uris, string $parentUri)
	{
		foreach ($routes as $route) {
			if (empty($route->attributes()->controller)) {
				throw new RuntimeException("Empty controller attribute", E_ERROR);
			}

			$currentUri = "";

			if (count($route->uri) == 0) {
				throw new RuntimeException("Missing uri for " . (string)$route->attributes()->controller . " in language-country: " . $code, E_ERROR);
			} else {
				foreach ($route->uri as $uri) {
					if (empty($uri->attributes()->value)) {
						throw new RuntimeException("Empty value attribute", E_ERROR);
					}

					if (!preg_match(RegExp::getLocalCode(), $uri->attributes()->code) and $uri->attributes()->code != "*") {
						throw new RuntimeException("Invalid code attribute", E_ERROR);
					}

					if ($uri->attributes()->code == $code or $uri->attributes()->code == "*") {
						$uriStr = (string)$uri->attributes()->value;

						if (!preg_match("/\.[a-zA-Z0-9]{2,4}$/", $uriStr) and substr($uriStr, -1) != "/") {
							$uriStr .= "/";
						}

						if (substr($parentUri, -1) != "/") {
							$parentUri .= "/";
						}

						$currentUri = str_replace("//", "/", $parentUri . $uriStr);

						$isRegExp = (isset($uri->attributes()->regExp) and $uri->attributes()->regExp == 'true') ? true : false;

						if (isset($uris[$currentUri])) {
							throw new RuntimeException("Duplicate uri: " . $currentUri . " in language-country: " . $code, E_ERROR);
						} else {
							$uris[$currentUri] = [
								'parentUri' => $parentUri,
								'regExp' => $isRegExp,
								'controller' => (string)$route->attributes()->controller
							];
							break;
						}
					}
				}

				if (isset($route->subroutes)) {
					$uris = $this->parseRoutes($route->subroutes->route, $code, $uris, $currentUri);
				}
			}
		}
		return $uris;
	}

	/**
	 * Search for the correct controller to a given uri.
	 *
	 * @param string $uri
	 *
	 * @return array|null
	 */
	public function getControllerByUri(string $uri)
	{
		if (isset($this->uris[$uri])) {
			return [
				'controller' => $this->uris[$uri]['controller'],
				'regExpMatches' => []
			];
		} else {
			foreach ($this->uris as $regExpUri => $uriParameter) {
				if ($uriParameter['regExp'] === true) {
					$regExpMatches = [];
					$regExpUri = str_replace("/", "\/", $regExpUri);

					if (preg_match("/^" . $regExpUri . "$/", $uri, $regExpMatches)) {
						return [
							'controller' => $uriParameter['controller'],
							'regExpMatches' => $regExpMatches
						];
					}
				}
			}
			return null;
		}
	}

	/**
	 * Return the full routes array
	 *
	 * @return array
	 */
	public function getRoutes()
	{
		return $this->uris;
	}
}