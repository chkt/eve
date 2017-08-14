<?php

namespace eve\access;

use eve\common\IGenerateable;
use eve\common\IProjectable;



interface ITraversableAccessor
extends IItemAccessor, IGenerateable, IProjectable
{

	public function isEqual(ITraversableAccessor $b) : bool;
}
