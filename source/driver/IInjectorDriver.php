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

	public function getHost() : IInjectorHost;


	public function getFactory() : ICoreFactory;

	public function getAccessorFactory() : ISimpleFactory;


	public function getEntityParser() : IEntityParser;

	public function getReferences() : IItemAccessor;

	public function getInstanceCache() : IItemMutator;
}
