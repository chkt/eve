<?php

namespace eve\inject\resolve;

use eve\common\access\ITraversableAccessor;
use eve\common\assembly\IAssemblyHost;
use eve\inject\IInjectableFactory;
use eve\inject\IInjector;



final class FactoryResolver
implements IInjectorResolver
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		return [[
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $config->getItem('driver')
		]];
	}



	private $_driver;


	public function __construct(IAssemblyHost $driver) {
		$this->_driver = $driver;
	}


	private function _getInstanceConfig(ITraversableAccessor $data) : ITraversableAccessor {
		$accessor = $this->_driver->getItem('accessorFactory');

		if ($data->hasKey('config')) return $accessor->select($data, 'config');
		else return $accessor->produce(($data = []));
	}


	public function produce(ITraversableAccessor $config) {
		$qname = $config->getItem('factory');

		$fab = $this->_driver
			->getItem('injector')
			->produce($qname);

		if (!($fab instanceof IInjectableFactory)) throw new \ErrorException(sprintf('INJ not a factory "%s"', $qname));

		return $fab->produce($this->_getInstanceConfig($config));
	}
}
