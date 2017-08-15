<?php

namespace eve\driver;

use eve\inject\IInjector;
use eve\provide\ILocator;



class InjectorHost
implements IInjectorHost
{

	private $_injector;
	private $_locator;


	public function __construct(IInjectorDriver $driver) {
		$this->_injector = $driver->getInjector();
		$this->_locator = $driver->getLocator();
	}


	public function hasKey(string $key) : bool {
		return in_array($key, [
			self::ITEM_INJECTOR,
			self::ITEM_LOCATOR
		]);
	}


	public function getItem(string $key) {
		if ($key === self::ITEM_INJECTOR) return $this->getInjector();
		else if ($key === self::ITEM_LOCATOR) return $this->getLocator();
		else throw new \ErrorException(sprintf('ACC invalid key "%s"', $key));
	}


	public function getInjector() : IInjector {
		return $this->_injector;
	}

	public function getLocator() : ILocator {
		return $this->_locator;
	}
}
