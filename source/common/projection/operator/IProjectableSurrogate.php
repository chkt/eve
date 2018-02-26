<?php

namespace eve\common\projection\operator;

use eve\common\projection\IProjectable;



interface IProjectableSurrogate
{

	public function isEqual(IProjectable $a, IProjectable $b) : bool;
}
