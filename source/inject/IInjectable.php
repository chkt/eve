<?php

namespace eve\inject;

use eve\access\ITraversableAccessor;



interface IInjectable
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array;
}
