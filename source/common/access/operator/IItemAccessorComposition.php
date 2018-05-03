<?php

namespace eve\common\access\operator;

use eve\common\access\IItemAccessor;



interface IItemAccessorComposition
{

	public function select(IItemAccessor $source, string $key) : IItemAccessor;
}
