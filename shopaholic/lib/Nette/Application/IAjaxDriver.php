<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2009 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Application
 * @version    $Id: IAjaxDriver.php 204 2009-02-02 18:27:51Z david@grudl.com $
 */



/**
 * AJAX strategy interface.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Application
 */
interface IAjaxDriver
{

	/**
	 * @param  IHttpResponse
	 * @return void
	 */
	function open(IHttpResponse $httpResponse);

	/**
	 * @return void
	 */
	function close();

}
