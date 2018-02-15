<?php

namespace eve\common\access;

use eve\common\IGenerateable;
use eve\common\projection\IProjectable;



interface ITraversableAccessor
extends IItemAccessor, IGenerateable, IProjectable
{

	public function isEqual(ITraversableAccessor $b) : bool;
}
