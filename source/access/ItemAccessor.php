<?php

namespace eve\access;



class ItemAccessor
implements IItemAccessor
{

	private $_data;


	public function __construct(array& $data) {
		$this->_data =& $data;
	}


	protected function& _useData() : array {
		return $this->_data;
	}


	public function hasKey(string $key) : bool {
		return array_key_exists($key, $this->_data);
	}


	public function getItem(string $key) {
		if (!array_key_exists($key, $this->_data)) throw new \ErrorException(sprintf('ACC invalid key "%s"', $key));

		return $this->_data[$key];
	}
}
