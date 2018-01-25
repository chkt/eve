<?php

namespace eve\common\access;



interface IItemMutator
extends IItemAccessor, IKeyMutator
{

	public function setItem(string $id, $item) : IItemMutator;
}
