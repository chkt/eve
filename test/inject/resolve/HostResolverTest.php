<?php

namespace test\inject\resolve;

use PHPUnit\Framework\TestCase;

use eve\common\IFactory;
use eve\common\factory\IAccessorFactory;
use eve\common\access\TraversableAccessor;
use eve\common\access\exception\IAccessorException;
use eve\driver\IInjectorDriver;
use eve\inject\IInjectable;
use eve\inject\IInjector;
use eve\inject\resolve\IInjectorResolver;
use eve\inject\resolve\HostResolver;
use eve\provide\ILocator;



class HostResolverTest
extends TestCase
{

	private function _mockInjector() : IInjector {
		return $this->getMockBuilder(IInjector::class)->getMock();
	}

	private function _mockLocator() : ILocator {
		return $this->getMockBuilder(ILocator::class)->getMock();
	}

	private function _mockDriver(IInjector $injector = null, ILocator $locator = null) : IInjectorDriver {
		if (is_null($injector)) $injector = $this->_mockInjector();
		if (is_null($locator)) $locator = $this->_mockLocator();

		$ins = $this
			->getMockBuilder(IInjectorDriver::class)
			->getMock();

		$ins
			->expects($this->any())
			->method('getItem')
			->with($this->isType('string'))
			->willReturnCallback(function(string $key) use ($injector, $locator) {
				if ($key === IInjectorDriver::ITEM_INJECTOR) return $injector;
				else if ($key === IInjectorDriver::ITEM_LOCATOR) return $locator;
				else throw new \ErrorException();
			});

		return $ins;
	}

	private function _produceAccessor(array $data) {
		return new TraversableAccessor($data);
	}

	private function _produceResolver(IInjectorDriver $host = null) : HostResolver {
		if (is_null($host)) $host = $this->_mockDriver();

		return new HostResolver($host);
	}


	public function testDependencyConfig() {
		$driver = $this->_mockDriver();

		$this->assertEquals([[
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $driver
		]], HostResolver::getDependencyConfig($this->_produceAccessor([
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
		$injector = $this->_mockInjector();
		$locator = $this->_mockLocator();
		$driver = $this->_mockDriver($injector, $locator);
		$resolver = $this->_produceResolver($driver);

		$this->assertSame($injector, $resolver->produce($this->_produceAccessor([ 'type' => IInjectorDriver::ITEM_INJECTOR ])));
		$this->assertSame($locator, $resolver->produce($this->_produceAccessor([ 'type' => IInjectorDriver::ITEM_LOCATOR ])));
	}


	public function testProduce_invalidAccessor() {
		$resolver = $this->_produceResolver();
		$accessor = $this->_produceAccessor([]);

		$this->expectException(IAccessorException::class);
		$this->expectExceptionMessage('ACC invalid key "type"');

		$resolver->produce($accessor);
	}


	public function testProduce_noReference() {
		$resolver = $this->_produceResolver();
		$accessor = $this->_produceAccessor([ 'type' => 'foo' ]);

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('INJ not resolvable "foo"');

		$resolver->produce($accessor);
	}
}
