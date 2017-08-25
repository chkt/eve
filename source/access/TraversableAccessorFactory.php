<?php

namespace eve\access;

use eve\common\ISimpleFactory;
use eve\factory\ICoreFactory;



final class TraversableAccessorFactory
implements ISimpleFactory
{

	private $_fab;


	public function __construct(ICoreFactory $fab) {
		$this->_fab = $fab;
	}


	public function instance(array& $data) {
		return $this->_fab->newInstance(TraversableAccessor::class, [ & $data ]);
	}
}
