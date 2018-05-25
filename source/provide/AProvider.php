<?php

namespace eve\provide;

use eve\common\factory\IBaseFactory;
use eve\common\access\ITraversableAccessor;
use eve\inject\IInjectable;
use eve\inject\IInjector;



abstract class AProvider
implements IProvider
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		$driver = $config->getItem('driver');

		return [[
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $driver->getItem('injector')
		], [
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $driver->getItem('baseFactory')
		]];
	}



	private $_injector;
	private $_baseFactory;


	public function __construct(IInjector $injector, IBaseFactory $baseFactory) {
		$this->_injector = $injector;
		$this->_baseFactory = $baseFactory;
	}


	abstract protected function _parseEntity(string $entity) : array;


	protected function _getParts(string $entity) : array {
		$parts = $this->_parseEntity($entity);

		if (
			!array_key_exists('qname', $parts) ||
			!is_string($parts['qname']) ||
			!array_key_exists('config', $parts) ||
			!is_array($parts['config'])
		) throw new \ErrorException(sprintf('PRV malformed entity "%s"', $entity));

		return $parts;
	}


	public function hasKey(string $key) : bool {
		try {
			$parts = $this->_getParts($key);
		}
		catch (\Exception $ex) {
			return false;
		}

		return $this->_baseFactory->hasInterface($parts['qname'], IInjectable::class);
	}

	public function getItem(string $key) {
		$parts = $this->_getParts($key);

		return $this->_injector->produce($parts['qname'], $parts['config']);
	}
}
