<?php

namespace eve\inject\resolve;

use eve\access\ITraversableAccessor;



final class ArgumentResolver
implements IInjectorResolver
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		return [];
	}



	public function produce(ITraversableAccessor $config) {
		return $config->getItem('data');
	}
}
