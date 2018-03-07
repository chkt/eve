<?php

namespace test\common\access\exception;

use PHPUnit\Framework\TestCase;

use eve\common\access\exception\AAccessorException;



final class AAccessorExceptionTest
extends TestCase
{

	private function _mockException(string $key = '', \Throwable $previous = null, string $msg = 'null') {
		$ex = $this
			->getMockBuilder(AAccessorException::class)
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$ex
			->method('_produceMessage')
			->willReturn($msg);

		$ex->__construct($key, $previous);

		return $ex;
	}

	public function testInheritance() {
		$ex = $this->_mockException();

		$this->assertInstanceOf(\eve\common\access\exception\IAccessorException::class, $ex);
		$this->assertInstanceOf(\Exception::class, $ex);
		$this->assertInstanceOf(\Throwable::class, $ex);

	}

	public function testGetMessage() {
		$ex = $this->_mockException('bar', null, 'foo "%s"');

		$this->assertEquals('foo "bar"', $ex->getMessage());
	}

	public function testGetPrevious() {
		$prev = new \Exception();
		$ex = $this->_mockException('', $prev);

		$this->assertSame($prev, $ex->getPrevious());
	}

	public function testGetKey() {
		$ex = $this->_mockException('foo');

		$this->assertEquals('foo', $ex->getKey());
	}
}
