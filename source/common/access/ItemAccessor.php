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


	protected function _handleAccessorException(AccessorException $ex) : bool {
		return false;
	}


	final protected function& _useData() : array {
		return $this->_data;
	}


	public function hasKey(string $key) : bool {
		return array_key_exists($key, $this->_data);
	}


	public function getItem(string $key) {
		if (!array_key_exists($key, $this->_data)) {
			$ex = new AccessorException($key);

			if (
				!$this->_handleAccessorException($ex) ||
				!array_key_exists($key, $this->_data)
			) throw $ex;
		}

		return $this->_data[$key];
	}
}
