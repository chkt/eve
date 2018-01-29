<?php

namespace test\inject\cache;

use PHPUnit\Framework\TestCase;

use eve\common\factory\ICoreFactory;
use eve\common\access\ITraversableAccessor;
use eve\inject\IInjectableIdentity;
use eve\inject\cache\IKeyEncoder;
use eve\inject\cache\KeyEncoder;



final class KeyEncoderTest
extends TestCase
{

	private function _mockBaseFactory() {
		return $this
			->getMockBuilder(ICoreFactory::class)
			->getMock();
	}

	private function _mockAccessor() {
		return $this
			->getMockBuilder(ITraversableAccessor::class)
			->getMock();
	}


	private function _produceKeyEncoder(ICoreFactory $base = null) {
		if (is_null($base)) $base = $this->_mockBaseFactory();

		return new KeyEncoder($base);
	}


	public function testInheritance() {
		$encoder = $this->_produceKeyEncoder();

		$this->assertInstanceOf(IKeyEncoder::class, $encoder);
	}


	public function testEncodeIdentity() {
		$base = $this->_mockBaseFactory();

		$base
			->expects($this->any())
			->method('hasInterface')
			->with(
				$this->equalTo('foo'),
				$this->equalTo(IInjectableIdentity::class)
			)
			->willReturn(true);

		$base
			->expects($this->any())
			->method('callMethod')
			->with(
				$this->equalTo('foo'),
				$this->equalTo('getInstanceIdentity'),
				$this->callback(function($arg) {
					return is_array($arg) && count($arg) === 1 && $arg[0] instanceof ITraversableAccessor;
				})
			)
			->willReturn('bar');

		$encoder = $this->_produceKeyEncoder($base);
		$config = $this->_mockAccessor();

		$this->assertEquals('foo:bar', $encoder->encodeIdentity('foo', $config));
	}

	public function testBuildKey_noInterface() {
		$base = $this->_mockBaseFactory();

		$base
			->method('hasInterface')
			->with(
				$this->equalTo('foo'),
				$this->equalTo(IInjectableIdentity::class)
			)
			->willReturn(false);

		$base
			->expects($this->never())
			->method('callMethod');

		$encoder = $this->_produceKeyEncoder($base);
		$config = $this->_mockAccessor();

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('KEY cannot create key for "foo"');

		$encoder->encodeIdentity('foo', $config);
	}

	public function testGetKey() {
		$encoder = $this->_produceKeyEncoder();

		$this->assertEquals('foo:bar', $encoder->encode('foo', 'bar'));
	}
}
