<?php

namespace eve\common\access;

use eve\common\projection\IProjectable;



class TraversableAccessor
extends ItemAccessor
implements ITraversableAccessor
{

	public function isEqual(IProjectable $b) : bool {
		return $this->_useData() === $b->getProjection();
	}


	public function iterate() : \Generator {
		foreach ($this->_useData() as $key => $value) yield $key => $value;
	}


	public function getProjection() : array {
		return $this->_useData();
	}
}
