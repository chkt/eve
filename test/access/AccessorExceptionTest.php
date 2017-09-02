<?php

namespace test\access;

use PHPUnit\Framework\TestCase;

use eve\access\IAccessorException;
use eve\access\AccessorException;



final class AccessorExceptionTest
extends TestCase
{

	private function _produceException(string $key = '') {
		return new AccessorException($key);
	}


	public function testInheritance() {
		$ins = $this->_produceException();

		$this->assertInstanceOf(IAccessorException::class, $ins);
	}


	public function testGetMessage() {
		$ins = $this->_produceException('foo');

		$this->assertEquals('ACC invalid key "foo"', $ins->getMessage());
	}

	public function testGetKey() {
		$ins = $this->_produceException('foo');

		$this->assertEquals('foo', $ins->getKey());
	}
}
