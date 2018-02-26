<?php

namespace eve\common\access;

use eve\common\factory\ICoreFactory;
use eve\common\access\operator\AItemAccessorComposition;



final class TraversableAccessorFactory
extends AItemAccessorComposition
{

	private $_baseFactory;


	public function __construct(ICoreFactory $baseFactory) {
		$this->_baseFactory = $baseFactory;
	}


	public function produce(array& $data) {
		return $this->_baseFactory->newInstance(TraversableAccessor::class, [ & $data ]);
	}
}
