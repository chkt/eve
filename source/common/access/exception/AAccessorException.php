<?php

namespace eve\common\access\exception;

use eve\common\access\IAccessorException;



abstract class AAccessorException
extends \Exception
implements IAccessorException
{

	private $_key;


	public function __construct(string $key, \Throwable $previous = null) {
		$message = sprintf($this->_produceMessage(), $key);

		parent::__construct($message, 0, $previous);

		$this->_key = $key;
	}


	abstract protected function _produceMessage() : string;


	public function getKey() : string {
		return $this->_key;
	}
}
