<?php

namespace eve\common\factory;

use eve\common\IFactory;
use eve\access\ITraversableAccessor;



interface IAccessorFactory
extends IFactory
{

	public function produce(ITraversableAccessor $config);
}
