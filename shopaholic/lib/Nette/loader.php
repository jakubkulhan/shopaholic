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
 * @package    Nette
 * @version    $Id: loader.php 251 2009-03-31 17:10:17Z david@grudl.com $
 */

require_once dirname(__FILE__) . '/compatibility.php';

require_once dirname(__FILE__) . '/Loaders/NetteLoader.php';


NetteLoader::getInstance()->base = dirname(__FILE__);
NetteLoader::getInstance()->register();
