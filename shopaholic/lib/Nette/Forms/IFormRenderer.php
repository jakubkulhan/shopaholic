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
 * @package    Nette\Forms
 * @version    $Id: IFormRenderer.php 182 2008-12-31 00:28:33Z david@grudl.com $
 */



/**
 * Defines method that must implement form rendered.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Forms
 */
interface IFormRenderer
{

	/**
	 * Provides complete form rendering.
	 * @param  Form
	 * @return string
	 */
	function render(Form $form);

}
