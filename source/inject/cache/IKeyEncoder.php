<?php

namespace eve\inject\cache;

use eve\common\access\ITraversableAccessor;



interface IKeyEncoder
{

	public function encode(string $qname, string $id) : string;

	public function encodeIdentity(string $qname, ITraversableAccessor $config) : string;
}
