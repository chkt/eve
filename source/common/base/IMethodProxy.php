<?php

namespace eve\common\base;



interface IMethodProxy
{

	public function callMethod(string $qname, string $method, array $args = []);
}
