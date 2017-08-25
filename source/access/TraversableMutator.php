<?php

namespace eve\access;



class TraversableMutator
extends TraversableAccessor
implements IItemMutator
{

	private $_data;


	public function __construct(array& $data) {
		parent::__construct($data);

		$this->_data =& $data;
	}


	public function removeKey(string $key) : IKeyMutator {
		if (array_key_exists($key, $this->_data)) unset($this->_data[$key]);

		return $this;
	}

	public function setItem(string $id, $item) : IItemMutator {
		$this->_data[$id] = $item;

		return $this;
	}
}
