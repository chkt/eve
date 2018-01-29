<?php

namespace eve\driver;

use eve\common\factory\ISimpleFactory;
use eve\common\factory\ICoreFactory;
use eve\common\access\IItemMutator;
use eve\common\access\ItemAccessor;
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


	public function getInstanceCache() : IItemMutator {
		return $this->getItem(self::ITEM_INSTANCE_CACHE);
	}
}
