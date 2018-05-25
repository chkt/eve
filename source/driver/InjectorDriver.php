<?php

namespace eve\driver;

use eve\common\assembly\IAssemblyHost;
use eve\common\factory\ISimpleFactory;
use eve\common\factory\IBaseFactory;
use eve\common\access\IItemMutator;
use eve\common\access\exception\AccessorException;
use eve\inject\IInjector;
use eve\inject\cache\IKeyEncoder;
use eve\provide\ILocator;
use eve\entity\IEntityParser;



class InjectorDriver
implements IInjectorDriver
{

	private $_assembly;


	public function __construct(IAssemblyHost $assembly) {
		$this->_assembly = $assembly;
	}


	public function hasKey(string $key) : bool {
		return in_array($key, [
			self::ITEM_BASE_FACTORY,
			self::ITEM_ACCESSOR_FACTORY,
			self::ITEM_KEY_ENCODER,
			self::ITEM_INSTANCE_CACHE,
			self::ITEM_INJECTOR,
			self::ITEM_ENTITY_PARSER,
			self::ITEM_LOCATOR
		]);
	}

	public function getItem(string $key) {
		if (!$this->hasKey($key)) throw new AccessorException($key);

		return $this->_assembly->getItem($key);
	}


	public function getBaseFactory() : IBaseFactory {
		return $this->_assembly->getItem(self::ITEM_BASE_FACTORY);
	}

	public function getAccessorFactory() : ISimpleFactory {
		return $this->_assembly->getItem(self::ITEM_ACCESSOR_FACTORY);
	}

	public function getKeyEncoder() : IKeyEncoder {
		return $this->_assembly->getItem(self::ITEM_KEY_ENCODER);
	}

	public function getInstanceCache() : IItemMutator {
		return $this->_assembly->getItem(self::ITEM_INSTANCE_CACHE);
	}

	public function getInjector() : IInjector {
		return $this->_assembly->getItem(self::ITEM_INJECTOR);
	}

	public function getEntityParser() : IEntityParser {
		return $this->_assembly->getItem(self::ITEM_ENTITY_PARSER);
	}

	public function getLocator() : ILocator {
		return $this->_assembly->getItem(self::ITEM_LOCATOR);
	}
}
