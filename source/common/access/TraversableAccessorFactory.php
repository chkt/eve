<?php

namespace eve\common\access;

use eve\common\factory\ICoreFactory;
use eve\common\access\operator\AItemAccessorSurrogate;



final class TraversableAccessorFactory
extends AItemAccessorSurrogate
{

	private $_baseFactory;


	public function __construct(ICoreFactory $baseFactory) {
		$this->_baseFactory = $baseFactory;
	}


	public function produce(array& $data) {
		return $this->_baseFactory->newInstance(TraversableAccessor::class, [ & $data ]);
	}
}
