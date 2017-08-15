<?php

namespace eve\provide;

use eve\access\ITraversableAccessor;
use eve\driver\IInjectorDriver;
use eve\inject\IInjector;



class ProviderProvider
implements ILocator
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		return [[
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $config->getItem('driver')
		], [
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $config->getItem('providerNames')
		]];
	}



	private $_driver;

	private $_injector;
	private $_parser;

	private $_providerNames;
	private $_providers;


	public function __construct(IInjectorDriver $driver, array $providerNames) {
		$this->_driver = $driver;
		$this->_injector = $driver->getInjector();
		$this->_parser = $driver->getEntityParser();

		$this->_providerNames = $providerNames;
		$this->_providers = [];
	}


	public function hasKey(string $key) : bool {
		return array_key_exists($key, $this->_providerNames);
	}


	public function getItem(string $key) {
		if (!array_key_exists($key, $this->_providers)) {
			if (!array_key_exists($key, $this->_providerNames)) throw new \ErrorException(sprintf('LOC unknown provider "%s"', $key));

			$this->_providers[$key] = $this->_injector->produce($this->_providerNames[$key], [ 'driver' => $this->_driver ]);
		}		//TODO: test IProvider inheritance

		return $this->_providers[$key];
	}


	public function locate(string $entity) {
		$config = $this->_parser->parse($entity);

		return $this
			->getItem($config['type'])
			->getItem($config['location']);
	}
}
