<?php

namespace eve\common\access;

use eve\common\access\exception\AccessorException;



class ItemAccessor
implements IItemAccessor
{

	private $_data;


	public function __construct(array& $data) {
		$this->_data =& $data;
	}


	protected function _handleAccessFailure(array& $data, string $key) {}


	final protected function& _useData() : array {
		return $this->_data;
	}


	public function hasKey(string $key) : bool {
		return array_key_exists($key, $this->_data);
	}


	public function getItem(string $key) {
		$data =& $this->_data;

		if (!array_key_exists($key, $data)) {
			$this->_handleAccessFailure($data, $key);

			if (!array_key_exists($key, $data)) throw new AccessorException($key);
		}

		return $data[$key];
	}
}
