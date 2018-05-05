<?php

namespace eve\provide;

use eve\common\access\ITraversableAccessor;
use eve\common\assembly\IAssemblyHost;
use eve\inject\IInjector;



class ProviderProvider
implements ILocator
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		return [[
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $config->getItem('driver')
		]];
	}



	private $_parser;
	private $_providers;


	public function __construct(IAssemblyHost $driver) {
		$this->_parser = $driver->getItem('entityParser');
		$this->_providers = $driver->getItem('providerAssembly');
	}


	public function hasKey(string $key) : bool {
		return $this->_providers->hasKey($key);
	}


	public function getItem(string $key) {
		return $this->_providers->getItem($key);
	}


	public function locate(string $entity) {
		$config = $this->_parser->parse($entity);

		return $this->_providers
			->getItem($config['type'])
			->getItem($config['location']);
	}
}
