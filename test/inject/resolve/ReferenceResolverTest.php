<?php

namespace test\inject\resolve;

use PHPUnit\Framework\TestCase;

use eve\common\IFactory;
use eve\common\IAccessorFactory;
use eve\access\IAccessorException;
use eve\access\ITraversableAccessor;
use eve\access\TraversableAccessor;
use eve\driver\IInjectorDriver;
use eve\inject\IInjectable;
use eve\inject\IInjector;
use eve\inject\resolve\IInjectorResolver;
use eve\inject\resolve\ReferenceResolver;



final class ReferenceResolverTest
extends TestCase
{

	private function _mockDriver(TraversableAccessor $refs = null) : IInjectorDriver {
		if (is_null($refs)) $refs = $this->_produceAccessor([]);

		$ins = $this
			->getMockBuilder(IInjectorDriver::class)
			->getMock();

		$ins
			->expects($this->once())
			->method('getReferences')
			->with()
			->willReturn($refs);

		return $ins;
	}

	private function _produceAccessor(array $data) {
		return new TraversableAccessor($data);
	}

	private function _produceResolver(ITraversableAccessor $references = null) {
		if (is_null($references)) $references = $this->_produceAccessor([]);

		return new ReferenceResolver($references);
	}


	public function testDependencyConfig() {
		$refs = $this->_produceAccessor([]);
		$driver = $this->_mockDriver($refs);

		$this->assertEquals([[
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $refs
		]], ReferenceResolver::getDependencyConfig($this->_produceAccessor([
			'driver' => $driver
		])));
	}

	public function testInheritance() {
		$resolver = $this->_produceResolver();

		$this->assertInstanceOf(IInjectorResolver::class, $resolver);
		$this->assertInstanceOf(IInjectable::class, $resolver);
		$this->assertInstanceOf(IAccessorFactory::class, $resolver);
		$this->assertInstanceOf(IFactory::class, $resolver);
	}


	public function testProduce() {
		$refs = $this->_produceAccessor([ 'foo' => 1 ]);
		$resolver = $this->_produceResolver($refs);
		$accessor = $this->_produceAccessor([ 'name' => 'foo' ]);

		$this->assertEquals(1, $resolver->produce($accessor));
	}

	public function testProduce_invalidAccessor() {
		$resolver = $this->_produceResolver();
		$accessor = $this->_produceAccessor([]);

		$this->expectException(IAccessorException::class);
		$this->expectExceptionMessage('ACC invalid key "name"');

		$resolver->produce($accessor);
	}

	public function testProduce_noReference() {
		$resolver = $this->_produceResolver();
		$accessor = $this->_produceAccessor([ 'name' => 'foo' ]);

		$this->expectException(IAccessorException::class);
		$this->expectExceptionMessage('ACC invalid key "foo"');

		$resolver->produce($accessor);
	}
}
