<?php

namespace eve\access;



class TraversableAccessor
implements ITraversableAccessor
{

	private $_data;


	public function __construct(array& $data) {
		$this->_data =& $data;
	}


	public function isEqual(ITraversableAccessor $b) : bool {
		$data = $b instanceof TraversableAccessor ? $b->_data : $b->getProjection();

		return $this->_data === $data;
	}


	public function hasKey(string $key) : bool {
		return array_key_exists($key, $this->_data);
	}

	public function getItem(string $key) {
		if (!array_key_exists($key, $this->_data)) throw new \ErrorException(sprintf('ACC invalid key "%s"', $key));

		return $this->_data[$key];
	}


	public function& iterate() : \Generator {
		foreach ($this->_data as $key => $value) yield $key => $value;
	}


	public function getProjection(array $selector = null) : array {
		return $this->_data;
	}
}
