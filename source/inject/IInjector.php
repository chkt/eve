<?php

namespace eve\inject;



interface IInjector
{

	const TYPE_ARGUMENT = 'argument';
	const TYPE_RESOLVE = 'resolve';
	const TYPE_INJECTOR = 'injector';
	const TYPE_LOCATOR = 'locator';
	const TYPE_FACTORY = 'factory';



	public function produce(string $qname, array $config = []);
}
