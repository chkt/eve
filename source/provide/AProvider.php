<?php

namespace eve\provide;

use eve\access\ITraversableAccessor;
use eve\inject\IInjector;



abstract class AProvider
implements IProvider
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		return [[
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $config
				->getItem('driver')
				->getInjector()
		]];
	}



	private $_injector;


	public function __construct(IInjector $injector) {
		$this->_injector = $injector;
	}


	abstract protected function _parseEntity(string $entity) : array;


	public function hasKey(string $key) : bool {
		try {
			$this->getItem($key);

			return true;
		}
		catch (\ReflectionException $ex) {
			return false;
		}
	}

	public function getItem(string $key) {
		$parts = $this->_parseEntity($key);

		if (
			!array_key_exists('qname', $parts) ||
			!array_key_exists('config', $parts)
		) throw new \ErrorException(sprintf('PRV malformed entity "%s"', $key));

		return $this->_injector->produce($parts['qname'], $parts['config']);
	}
}
