<?php

namespace eve\driver;

use eve\common\ISimpleFactory;
use eve\common\IDriver;
use eve\factory\ICoreFactory;
use eve\access\IItemAccessor;
use eve\access\IItemMutator;
use eve\entity\IEntityParser;



interface IInjectorDriver
extends IDriver, IInjectorHost
{

	const ITEM_CORE_FACTORY = 'coreFactory';
	const ITEM_ACCESSOR_FACTORY = 'accessorFactory';
	const ITEM_ENTITY_PARSER = 'entityParser';
	const ITEM_REFERENCES = 'references';
	const ITEM_INSTANCE_CACHE = 'instanceCache';


	public function getCoreFactory() : ICoreFactory;

	public function getAccessorFactory() : ISimpleFactory;


	public function getEntityParser() : IEntityParser;


	public function getReferences() : IItemAccessor;

	public function getInstanceCache() : IItemMutator;
}
