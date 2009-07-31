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
 * @version    $Id: TextArea.php 220 2009-02-25 18:45:34Z david@grudl.com $
 */



require_once dirname(__FILE__) . '/../../Forms/Controls/TextBase.php';



/**
 * Multiline text input control.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Forms
 */
class TextArea extends TextBase
{

	/**
	 * @param  string  control name
	 * @param  string  label
	 * @param  int  width of the control
	 * @param  int  height of the control in text lines
	 */
	public function __construct($label, $cols = NULL, $rows = NULL)
	{
		parent::__construct($label);
		$this->control->setName('textarea');
		$this->control->cols = $cols;
		$this->control->rows = $rows;
		$this->value = '';
	}



	/**
	 * Generates control's HTML element.
	 * @return Html
	 */
	public function getControl()
	{
		$control = parent::getControl();
		$control->setText($this->value === '' ? $this->translate($this->emptyValue) : $this->tmpValue);
		return $control;
	}

}
