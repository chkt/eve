<?php

namespace eve\common\access;



class TraversableMutator
extends TraversableAccessor
implements IItemMutator
{

	public function removeKey(string $key) : IKeyMutator {
		$data =& $this->_useData();

		if (array_key_exists($key, $data)) unset($data[$key]);

		return $this;
	}

	public function setItem(string $id, $item) : IItemMutator {
		$this->_useData()[$id] = $item;

		return $this;
	}
}
