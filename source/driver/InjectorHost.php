<?php

namespace eve\driver;

use eve\access\ItemAccessor;
use eve\inject\IInjector;
use eve\provide\ILocator;



class InjectorHost
extends ItemAccessor
implements IInjectorHost
{

	public function __construct(IInjectorDriver $driver) {
		$data = [
			'injector' => $driver->getInjector(),
			'locator' => $driver->getLocator()
		];

		parent::__construct($data);
	}


	public function getInjector() : IInjector {
		return $this->getItem('injector');
	}

	public function getLocator() : ILocator {
		return $this->getItem('locator');
	}
}
