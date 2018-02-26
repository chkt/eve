<?php

namespace test\common\access;

use PHPUnit\Framework\TestCase;

use eve\common\access\exception\AAccessorException;
use eve\common\access\AccessorException;



final class AccessorExceptionTest
extends TestCase
{

	private function _produceException(string $key = '') {
		return new AccessorException($key);
	}


	public function testInheritance() {
		$ins = $this->_produceException();

		$this->assertInstanceOf(AAccessorException::class, $ins);
	}


	public function testGetMessage() {
		$ins = $this->_produceException('foo');

		$this->assertEquals('ACC invalid key "foo"', $ins->getMessage());
	}
}
