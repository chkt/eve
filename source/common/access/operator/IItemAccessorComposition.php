<?php

namespace eve\common\access\operator;

use eve\common\projection\IProjectable;
use eve\common\access\IItemAccessor;



interface IItemAccessorComposition
{

	public function select(IProjectable $source, string $key) : IItemAccessor;
}
