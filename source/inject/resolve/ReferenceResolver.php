<?php

namespace eve\inject\resolve;

use eve\common\access\ITraversableAccessor;
use eve\inject\IInjector;



final class ReferenceResolver
implements IInjectorResolver
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		return [[
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $config->getItem('driver')->getReferences()
		]];
	}



	private $_refs;


	public function __construct(ITraversableAccessor $references) {
		$this->_refs = $references;
	}


	public function produce(ITraversableAccessor $config) {
		return $this->_refs->getItem($config->getItem('name'));
	}
}
