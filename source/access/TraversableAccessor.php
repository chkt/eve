<?php

namespace eve\access;



class TraversableAccessor
extends ItemAccessor
implements ITraversableAccessor
{

	public function isEqual(ITraversableAccessor $b) : bool {
		$data = $b instanceof ItemAccessor ? $b->_useData() : $b->getProjection();

		return $this->_useData() === $data;
	}


	public function& iterate() : \Generator {
		foreach ($this->_useData() as $key => $value) yield $key => $value;
	}


	public function getProjection(array $selector = null) : array {
		return $this->_useData();
	}
}
