<?php

namespace eve\driver;

use eve\common\ISimpleFactory;
use eve\common\IDriver;
use eve\factory\IFactory;
use eve\access\IItemAccessor;
use eve\entity\IEntityParser;
use eve\inject\IInjector;
use eve\provide\ILocator;



interface IInjectorDriver
extends IDriver
{

	public function getHost() : IInjectorHost;

	public function getInjector() : IInjector;

	public function getLocator() : ILocator;


	public function getFactory() : IFactory;

	public function getAccessorFactory() : ISimpleFactory;


	public function getEntityParser() : IEntityParser;

	public function getReferences() : IITemAccessor;
}
