<?php
/**
 * Interace of the view controller
 *
 * @package        BASE
 * @subpackage     MVC
 * @author         Frederik Glücks <frederik@gluecks-gmbh.de>
 * @license        lgpl-3.0
 *
 */

namespace BASE\MVC\TemplateEngines;

/**
 * Interface ParentController
 *
 * @package        BASE\MVC\TemplateEngines
 * @author         Frederik Glücks <frederik@gluecks-gmbh.de>
 * @license        lgpl-3.0
 */
interface ParentController
{
	/**
	 * ParentController constructor.
	 *
	 * @param string $code
	 */
	public function __construct(string $code);
}
