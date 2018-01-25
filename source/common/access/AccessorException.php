<?php

namespace eve\common\access;



final class AccessorException
extends \Exception
implements IAccessorException
{

	private $_key;


	public function __construct(string $key) {
		parent::__construct(sprintf('ACC invalid key "%s"', $key));

		$this->_key = $key;
	}


	public function getKey() : string {
		return $this->_key;
	}
}
