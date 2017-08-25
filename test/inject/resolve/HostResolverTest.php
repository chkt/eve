<?php

namespace test\inject\resolve;

use PHPUnit\Framework\TestCase;

use eve\common\IFactory;
use eve\common\IAccessorFactory;
use eve\access\TraversableAccessor;
use eve\driver\IInjectorDriver;
use eve\driver\IInjectorHost;
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

	private function _mockDriver(IInjectorHost $host = null) : IInjectorDriver {
		if (is_null($host)) $host = $this->_mockHost();

		$ins = $this
			->getMockBuilder(IInjectorDriver::class)
			->getMock();

		$ins
			->expects($this->once())
			->method('getHost')
			->with()
			->willReturn($host);

		return $ins;
	}

	private function _mockHost(IInjector $injector = null, ILocator $locator = null) : IInjectorHost {
		if (is_null($injector)) $injector = $this->_mockInjector();
		if (is_null($locator)) $locator = $this->_mockLocator();

		$ins = $this
			->getMockBuilder(IInjectorHost::class)
			->getMock();

		$ins
			->expects($this->any())
			->method('getInjector')
			->with()
			->willReturn($injector);

		$ins
			->expects($this->any())
			->method('getLocator')
			->with()
			->willReturn($locator);

		return $ins;
	}

	private function _produceAccessor(array $data) {
		return new TraversableAccessor($data);
	}

	private function _produceResolver(IInjectorHost $host = null) : HostResolver {
		if (is_null($host)) $host = $this->_mockHost();

		return new HostResolver($host);
	}


	public function testDependencyConfig() {
		$host = $this->_mockHost();
		$driver = $this->_mockDriver($host);

		$this->assertEquals([[
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $host
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
		$host = $this->_mockHost($injector, $locator);
		$resolver = $this->_produceResolver($host);

		$this->assertSame($injector, $resolver->produce($this->_produceAccessor([ 'type' => 'injector' ])));
		$this->assertSame($locator, $resolver->produce($this->_produceAccessor([ 'type' => 'locator' ])));
	}


	public function testProduce_invalidAccessor() {
		$resolver = $this->_produceResolver();
		$accessor = $this->_produceAccessor([]);

		$this->expectException(\ErrorException::class);
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
