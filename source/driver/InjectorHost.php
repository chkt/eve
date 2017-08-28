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
			self::ITEM_INJECTOR => $driver->getInjector(),
			self::ITEM_LOCATOR => $driver->getLocator()
		];

		parent::__construct($data);
	}


	public function getInjector() : IInjector {
		return $this->getItem(self::ITEM_INJECTOR);
	}

	public function getLocator() : ILocator {
		return $this->getItem(self::ITEM_LOCATOR);
	}
}
