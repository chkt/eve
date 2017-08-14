<?php

namespace eve\common;



interface IGenerateable
{

	public function& iterate() : \Generator;
}
