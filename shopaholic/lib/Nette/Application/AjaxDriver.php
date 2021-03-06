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
 * @version    $Id: AjaxDriver.php 276 2009-04-16 10:02:42Z jakub.vrana $
 */



require_once dirname(__FILE__) . '/../Object.php';

require_once dirname(__FILE__) . '/../Application/IAjaxDriver.php';



/**
 * AJAX output strategy.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Application
 */
class AjaxDriver extends Object implements IAjaxDriver
{
	/** @var array */
	private $data;

	/** @var IHttpResponse */
	private $httpResponse;



	/**
	 * Generates link.
	 * @param  string
	 * @return string
	 */
	public function link($url)
	{
		return "return !nette.action(" . ($url === NULL ? "this.href" : json_encode($url)) . ", this)";
	}



	/**
	 * @param  IHttpResponse
	 * @return void
	 */
	public function open(IHttpResponse $httpResponse)
	{
		$httpResponse->expire(FALSE);
		$this->httpResponse = $httpResponse;
	}



	/**
	 * @return void
	 */
	public function close()
	{
		if ($this->data === NULL) {
			return; // response already handled by user?
		}

		$this->httpResponse->setContentType('application/x-javascript', 'utf-8');
		echo json_encode($this->data);
		$this->data = NULL;
	}



	/********************* AJAX response ****************d*g**/



	/**
	 * Sets a response parameter. Do not call directly.
	 * @param  string  name
	 * @param  mixed   value
	 * @return void
	 */
	public function __set($name, $value)
	{
		$this->data[$name] = $value;
	}



	/**
	 * Returns a response parameter. Do not call directly.
	 * @param  string  name
	 * @return mixed  value
	 */
	public function &__get($name)
	{
		return $this->data[$name];
	}



	/**
	 * Determines whether parameter is defined. Do not call directly.
	 * @param  string    name
	 * @return bool
	 */
	public function __isset($name)
	{
		return isset($this->data[$name]);
	}



	/**
	 * Removes a response parameter. Do not call directly.
	 * @param  string    name
	 * @return void
	 */
	public function __unset($name)
	{
		unset($this->data[$name]);
	}



	/** @deprecated */
	public function fireEvent($event, $arg)
	{
		trigger_error('Deprecated: use $presenter->ajaxDriver->events[] = array($event, $arg, ...); instead.', E_USER_WARNING);
		$args = func_get_args();
		array_shift($args);
		$this->data['events'][] = array('event' => $event, 'args' => $args);
	}



	/** @deprecated */
	public function updateSnippet($id, $content)
	{
		trigger_error('Deprecated: use $presenter->ajaxDriver->snippets[$id] = $content; instead.', E_USER_WARNING);
		$this->data['snippets'][$id] = (string) $content;
	}

}
