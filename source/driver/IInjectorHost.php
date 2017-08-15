<?php

namespace eve\driver;

use eve\common\IHost;
use eve\inject\IInjector;
use eve\provide\ILocator;



interface IInjectorHost
extends IHost
{

	const ITEM_INJECTOR = 'injector';
	const ITEM_LOCATOR = 'locator';



	public function getInjector() : IInjector;

	public function getLocator() : ILocator;
}
