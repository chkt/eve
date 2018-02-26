<?php

namespace eve\inject\resolve;

use eve\common\access\ITraversableAccessor;
use eve\inject\IInjector;
use eve\provide\ILocator;



final class ProviderResolver
implements IInjectorResolver
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		return [[
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $config
				->getItem('driver')
				->getItem('locator')
		]];
	}



	private $_locator;


	public function __construct(ILocator $locator) {
		$this->_locator = $locator;
	}


	public function produce(ITraversableAccessor $config) {
		return $this->_locator
			->getItem($config->getItem('type'))
			->getItem($config->getItem('location'));
	}
}
