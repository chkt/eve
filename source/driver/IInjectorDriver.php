<?php

namespace eve\driver;

use eve\common\IDriver;
use eve\common\factory\ISimpleFactory;
use eve\common\factory\IBaseFactory;
use eve\common\access\IItemMutator;
use eve\entity\IEntityParser;
use eve\inject\cache\IKeyEncoder;



interface IInjectorDriver
extends IDriver, IInjectorHost
{

	const ITEM_BASE_FACTORY = 'baseFactory';
	const ITEM_ACCESSOR_FACTORY = 'accessorFactory';
	const ITEM_ENTITY_PARSER = 'entityParser';
	const ITEM_KEY_ENCODER = 'keyEncoder';
	const ITEM_INSTANCE_CACHE = 'instanceCache';


	public function getBaseFactory() : IBaseFactory;

	public function getAccessorFactory() : ISimpleFactory;


	public function getEntityParser() : IEntityParser;

	public function getKeyEncoder() : IKeyEncoder;

	public function getInstanceCache() : IItemMutator;
}
