<?php

namespace eve\common\access;



interface IKeyAccessor
{

	public function hasKey(string $key) : bool;
}
