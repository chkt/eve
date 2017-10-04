<?php

namespace eve\driver;

use eve\access\ItemAccessor;

use eve\common\factory\ISimpleFactory;
use eve\factory\ICoreFactory;
use eve\access\IItemAccessor;
use eve\access\IItemMutator;
use eve\entity\IEntityParser;
use eve\inject\IInjector;
use eve\provide\ILocator;



class InjectorDriver
extends ItemAccessor
implements IInjectorDriver
{

	public function getInjector() : IInjector {
		return $this->getItem(self::ITEM_INJECTOR);
	}

	public function getLocator() : ILocator {
		return $this->getItem(self::ITEM_LOCATOR);
	}


	public function getEntityParser() : IEntityParser {
		return $this->getItem(self::ITEM_ENTITY_PARSER);
	}


	public function getCoreFactory() : ICoreFactory {
		return $this->getItem(self::ITEM_CORE_FACTORY);
	}

	public function getAccessorFactory() : ISimpleFactory {
		return $this->getItem(self::ITEM_ACCESSOR_FACTORY);
	}


	public function getReferences() : IItemAccessor {
		return $this->getItem(self::ITEM_REFERENCES);
	}

	public function getInstanceCache() : IItemMutator {
		return $this->getItem(self::ITEM_INSTANCE_CACHE);
	}
}
