<?php

namespace eve\access;



interface IKeyAccessor
{

	public function hasKey(string $key) : bool;
}
