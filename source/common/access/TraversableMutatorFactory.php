<?php

namespace eve\common\access;

use eve\common\factory\ISimpleFactory;
use eve\common\factory\ICoreFactory;



final class TraversableMutatorFactory
implements ISimpleFactory
{

	private $_fab;


	public function __construct(ICoreFactory $fab) {
		$this->_fab = $fab;
	}


	public function produce(array& $data) {
		return $this->_fab->newInstance(TraversableMutator::class, [ & $data ]);
	}
}