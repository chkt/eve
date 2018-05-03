<?php

namespace eve\common\access;



interface IItemMutator
extends IItemAccessor, IKeyMutator
{

	public function setItem(string $key, $item) : IItemMutator;
}
