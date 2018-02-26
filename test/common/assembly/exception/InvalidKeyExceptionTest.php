<?php

namespace test\common\assembly\exception;

use PHPUnit\Framework\TestCase;

use eve\common\access\exception\AAccessorException;
use eve\common\assembly\exception\IAssemblyException;
use eve\common\assembly\exception\InvalidKeyException;



final class InvalidKeyExceptionTest
extends TestCase
{

	private function _produceException(string $key = '') {
		return new InvalidKeyException($key);
	}


	public function testInheritance() {
		$ex = $this->_produceException();

		$this->assertInstanceOf(AAccessorException::class, $ex);
		$this->assertInstanceOf(IAssemblyException::class, $ex);
	}

	public function testGetMessage() {
		$ex = $this->_produceException('foo');

		$this->assertEquals('ASM invalid key "foo"', $ex->getMessage());
	}
}
