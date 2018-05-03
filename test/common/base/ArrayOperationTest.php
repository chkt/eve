<?php

namespace test\common\base;

use PHPUnit\Framework\TestCase;

use eve\common\base\ArrayOperation;



final class ArrayOperationTest
extends TestCase
{

	public function testMerge_props() {
		$a = [
			'foo' => 1,
			'bar' => 2
		];
		$b = [
			'foo' => 3,
			'baz' => 4
		];

		$this->assertSame([
			'foo' => 3,
			'bar' => 2,
			'baz' => 4
		], ArrayOperation::merge($a, $b));
	}

	public function testMerge_nested() {
		$a = [
			'foo' => [
				'bar' => 1,
				'baz' => 2
			]
		];

		$b = [
			'foo' => [
				'bar' => 3,
				'quux' => 4
			]
		];

		$this->assertSame([
			'foo' => [
				'bar' => 3,
				'baz' => 2,
				'quux' => 4
			]
		], ArrayOperation::merge($a, $b));
	}

	public function testMerge_override() {
		$a = [
			'foo' => 1,
			'bar' => []
		];
		$b = [
			'foo' => [],
			'bar' => 2
		];

		$this->assertSame($b, ArrayOperation::merge($a, $b));
	}
}
