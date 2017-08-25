<?php

namespace eve\factory;

use eve\common\IFactory;



interface ICoreFactory
extends IFactory
{

	public function hasInterface(string $qname, string $iname) : bool;


	public function newInstance(string $qname, array $args = []);


	public function callMethod(string $qname, string $method, array $args = []);
}
