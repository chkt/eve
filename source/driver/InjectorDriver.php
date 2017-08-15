<?php

namespace eve\driver;

use eve\access\TraversableAccessor;

use eve\common\ISimpleFactory;
use eve\factory\IFactory;
use eve\access\IItemAccessor;
use eve\entity\IEntityParser;
use eve\inject\IInjector;
use eve\provide\ILocator;



class InjectorDriver
extends TraversableAccessor
implements IInjectorDriver
{

	public function getHost() : IInjectorHost {
		return $this->getItem('host');
	}

	public function getInjector() : IInjector {
		return $this->getItem('injector');
	}

	public function getLocator() : ILocator {
		return $this->getItem('locator');
	}


	public function getEntityParser() : IEntityParser {
		return $this->getItem('entityParser');
	}


	public function getFactory() : IFactory {
		return $this->getItem('factory');
	}

	public function getAccessorFactory() : ISimpleFactory {
		return $this->getItem('accessorFactory');
	}


	public function getReferences() : IItemAccessor {
		return $this->getItem('references');
	}
}