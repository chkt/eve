<?php

namespace eve\common;



interface ITokenizer
{

	public function parse(string $source) : array;
}
