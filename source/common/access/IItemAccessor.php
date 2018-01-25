<?php

namespace eve\common\access;



interface IItemAccessor
extends IKeyAccessor
{

	public function getItem(string $key);
}
