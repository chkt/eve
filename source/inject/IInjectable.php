<?php

namespace eve\inject;

use eve\common\access\ITraversableAccessor;



interface IInjectable
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array;
}
