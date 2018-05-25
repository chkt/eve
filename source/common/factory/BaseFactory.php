<?php

namespace eve\common\factory;



final class BaseFactory
implements IBaseFactory
{

	public function hasInterface(string $qname, string $iname) : bool {
		return interface_exists($iname) && is_subclass_of($qname, $iname);
	}


	public function callMethod(string $qname, string $method, array $args = []) {
		return $qname::$method(...$args);
	}


	public function newInstance(string $qname, array $args = []) {
		return new $qname(...$args);
	}
}
