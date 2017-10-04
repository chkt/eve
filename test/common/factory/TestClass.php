<?php

namespace test\common\factory;



final class TestClass
extends TestParentClass
implements TestInterface
{

	static public function staticMethod(string $a, string $b, string $c) : string {
		return implode('/', [$a, $b, $c]);
	}



	public $foo;
	public $bar;
	public $baz;


	public function __construct(string $a, string $b, string $c) {
		$this->foo = $a;
		$this->bar = $b;
		$this->baz = $c;
	}


	public function memberMethod(string $a, string $b, string $c = '4') : string {
		return self::staticMethod($a, $b, $c);
	}

	public function thisMethod(string $a, string $b) : string {
		return self::staticMethod($a, $b, $this->baz);
	}
}
