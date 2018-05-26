<?php

namespace eve\inject;

use eve\common\factory\IInstancingFactory;



interface IInjector
extends IInstancingFactory
{

	const TYPE_ARGUMENT = 'argument';
	const TYPE_RESOLVE = 'resolve';
	const TYPE_INJECTOR = 'injector';
	const TYPE_LOCATOR = 'locator';
	const TYPE_FACTORY = 'factory';
}
