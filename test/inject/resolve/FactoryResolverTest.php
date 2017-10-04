<?php

namespace test\inject\resolve;

use PHPUnit\Framework\TestCase;

use eve\common\IFactory;
use eve\common\factory\IAccessorFactory;
use eve\access\IAccessorException;
use eve\access\IItemAccessor;
use eve\access\TraversableAccessor;
use eve\driver\IInjectorDriver;
use eve\inject\IInjector;
use eve\inject\IInjectable;
use eve\inject\IInjectableFactory;
use eve\inject\resolve\IInjectorResolver;
use eve\inject\resolve\FactoryResolver;




final class FactoryResolverTest
extends TestCase
{
	private function _mockInjector(array $map = []) : IInjector {
		$ins = $this
			->getMockBuilder(IInjector::class)
			->getMock();

		$ins
			->expects($this->any())
			->method('produce')
			->with($this->isType('string'))
			->willReturnCallback(function(string $qname) use ($map) {
				return $map[$qname];
			});

		return $ins;
	}

	private function _mockDriver(IInjector $injector = null) : IInjectorDriver {
		if (is_null($injector)) $injector = $this->_mockInjector();

		$ins = $this
			->getMockBuilder(IInjectorDriver::class)
			->getMock();

		$ins
			->expects($this->once())
			->method('getInjector')
			->with()
			->willReturn($injector);

		return $ins;
	}

	private function _mockFactory() : IInjectableFactory {
		$ins = $this
			->getMockBuilder(IInjectableFactory::class)
			->getMock();

		$ins
			->expects($this->any())
			->method('produce')
			->with($this->isInstanceOf(IItemAccessor::class))
			->willReturn('foo');

		return $ins;
	}

	private function _produceAccessor(array $data) {
		return new TraversableAccessor($data);
	}

	private function _produceResolver(IInjector $injector = null) {
		if (is_null($injector)) $injector = $this->_mockInjector();

		return new FactoryResolver($injector);
	}


	public function testInheritance() {
		$resolver = $this->_produceResolver();

		$this->assertInstanceOf(IInjectorResolver::class, $resolver);
		$this->assertInstanceOf(IInjectable::class, $resolver);
		$this->assertInstanceOf(IAccessorFactory::class, $resolver);
		$this->assertInstanceOf(IFactory::class, $resolver);
	}


	public function testDependencyConfig() {
		$injector = $this->_mockInjector();
		$driver = $this->_mockDriver($injector);

		$this->assertEquals([[
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $injector
		]], FactoryResolver::getDependencyConfig($this->_produceAccessor([
			'driver' => $driver
		])));
	}


	public function testProduce() {
		$injector = $this->_mockInjector([
			'qname' => $this->_mockFactory()
		]);
		$resolver = $this->_produceResolver($injector);
		$accessor = $this->_produceAccessor([ 'factory' => 'qname' ]);

		$this->assertEquals('foo', $resolver->produce($accessor));
	}

	public function testProduce_invalidAccessor() {
		$injector = $this->_mockInjector([
			'qname' => $this->_mockFactory()
		]);
		$resolver = $this->_produceResolver($injector);
		$accessor = $this->_produceAccessor([]);

		$this->expectException(IAccessorException::class);
		$this->expectExceptionMessage('ACC invalid key "factory"');

		$resolver->produce($accessor);
	}

	public function testProduce_invalidInterface() {
		$injector = $this->_mockInjector([
			'qname' => new \stdClass()
		]);
		$resolver = $this->_produceResolver($injector);
		$accessor = $this->_produceAccessor([ 'factory' => 'qname' ]);

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('INJ not a factory "qname"');

		$resolver->produce($accessor);
	}
}
