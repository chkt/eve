<?php

namespace eve\common\projection;



interface ICompactProjectable
extends IProjectable
{

	public function isEqual(IProjectable $b) : bool;
}
