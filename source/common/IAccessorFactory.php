<?php

namespace eve\common;

use eve\access\ITraversableAccessor;



interface IAccessorFactory
{

	public function produce(ITraversableAccessor $config);
}
