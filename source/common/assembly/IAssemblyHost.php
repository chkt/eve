<?php

namespace eve\common\assembly;

use eve\common\IFactory;
use eve\common\IHost;



interface IAssemblyHost
extends IFactory, IHost
{

	public function hasAssembled(string $key) : bool;
}
