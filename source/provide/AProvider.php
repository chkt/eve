<?php

namespace eve\provide;

use eve\common\factory\ICoreFactory;
use eve\access\ITraversableAccessor;
use eve\inject\IInjectable;
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
		], [
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $config
				->getItem('driver')
				->getCoreFactory()
		]];
	}



	private $_injector;
	private $_coreFactory;


	public function __construct(IInjector $injector, ICoreFactory $coreFactory) {
		$this->_injector = $injector;
		$this->_coreFactory = $coreFactory;
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

		return $this->_coreFactory->hasInterface($parts['qname'], IInjectable::class);
	}

	public function getItem(string $key) {
		$parts = $this->_getParts($key);

		return $this->_injector->produce($parts['qname'], $parts['config']);
	}
}
