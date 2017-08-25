<?php

namespace eve\common;

use eve\access\ITraversableAccessor;



interface IAccessorFactory
extends IFactory
{

	public function produce(ITraversableAccessor $config);
}
