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
 * @package    Nette\Collections
 * @version    $Id: ICollection.php 320 2009-05-25 15:07:17Z david@grudl.com $
 */



/**
 * Defines methods to manipulate generic collections.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Collections
 */
interface ICollection extends Countable, IteratorAggregate
{
	function append($item);
	function remove($item);
	function clear();
	function contains($item);
	//function IteratorAggregate::getIterator();
	//function Countable::count();
}
