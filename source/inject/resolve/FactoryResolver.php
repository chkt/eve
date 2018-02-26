<?php

namespace eve\inject\resolve;

use eve\common\access\ITraversableAccessor;
use eve\inject\IInjectableFactory;
use eve\inject\IInjector;



final class FactoryResolver
implements IInjectorResolver
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		return [[
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $config
				->getItem('driver')
				->getItem('injector')
		]];
	}



	private $_injector;


	public function __construct(IInjector $injector) {
		$this->_injector = $injector;
	}


	public function produce(ITraversableAccessor $config) {
		$fab = $this->_injector->produce($config->getItem('factory'));

		if (!($fab instanceof IInjectableFactory)) throw new \ErrorException(sprintf('INJ not a factory "%s"', $config->getItem('factory')));

		return $fab->produce($config);
	}
}
