<?php

namespace eve\access;

use eve\common\ISimpleFactory;
use eve\factory\ICoreFactory;



final class TraversableMutatorFactory
implements ISimpleFactory
{

	private $_fab;


	public function __construct(ICoreFactory $fab) {
		$this->_fab = $fab;
	}


	public function instance(array& $data) {
		return $this->_fab->newInstance(TraversableMutator::class, [ & $data ]);
	}
}
