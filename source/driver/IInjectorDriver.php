<?php

namespace eve\driver;

use eve\common\IDriver;
use eve\common\factory\ISimpleFactory;
use eve\common\factory\ICoreFactory;
use eve\common\access\IItemMutator;
use eve\entity\IEntityParser;
use eve\inject\cache\IKeyEncoder;



interface IInjectorDriver
extends IDriver, IInjectorHost
{

	const ITEM_CORE_FACTORY = 'coreFactory';
	const ITEM_ACCESSOR_FACTORY = 'accessorFactory';
	const ITEM_ENTITY_PARSER = 'entityParser';
	const ITEM_KEY_ENCODER = 'keyEncoder';
	const ITEM_INSTANCE_CACHE = 'instanceCache';


	public function getCoreFactory() : ICoreFactory;

	public function getAccessorFactory() : ISimpleFactory;


	public function getEntityParser() : IEntityParser;

	public function getKeyEncoder() : IKeyEncoder;

	public function getInstanceCache() : IItemMutator;
}
