<?php

namespace eve\access;



interface IItemAccessor
extends IKeyAccessor
{

	public function getItem(string $key);
}
