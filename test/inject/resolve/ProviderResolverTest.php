<?php

namespace test\inject\resolve;

use PHPUnit\Framework\TestCase;

use eve\common\IFactory;
use eve\common\factory\IAccessorFactory;
use eve\common\access\TraversableAccessor;
use eve\common\access\exception\IAccessorException;
use eve\common\assembly\IAssemblyHost;
use eve\inject\IInjectable;
use eve\inject\IInjector;
use eve\inject\resolve\IInjectorResolver;
use eve\inject\resolve\ProviderResolver;
use eve\provide\ILocator;



final class ProviderResolverTest
extends TestCase
{

	private function _mockLocator(array $map = []) : ILocator {
		$ins = $this
			->getMockBuilder(ILocator::class)
			->getMock();

		$ins
			->expects($this->any())
			->method('getItem')
			->with($this->isType('string'))
			->willReturnCallback(function(string $key) use ($map) {
				return $map[$key];
			});

		return $ins;
	}

	private function _mockDriverAssembly(ILocator $locator = null) : IAssemblyHost {
		if (is_null($locator)) $locator = $this->_mockLocator();

		$ins = $this
			->getMockBuilder(IAssemblyHost::class)
			->getMock();

		$ins
			->method('getItem')
			->with($this->equalTo('locator'))
			->willReturn($locator);

		return $ins;
	}

	private function _produceAccessor(array $data) {
		return new TraversableAccessor($data);
	}

	private function _produceResolver(ILocator $locator = null) {
		if (is_null($locator)) $locator = $this->_mockLocator();

		return new ProviderResolver($locator);
	}


	public function testDependencyConfig() {
		$locator = $this->_mockLocator();
		$driver = $this->_mockDriverAssembly($locator);

		$this->assertEquals([[
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $locator
		]], ProviderResolver::getDependencyConfig($this->_produceAccessor([
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
		$locator = $this->_mockLocator([
			'foo' => $this->_produceAccessor([
				'bar' => 1
			])
		]);
		$resolver = $this->_produceResolver($locator);
		$accessor = $this->_produceAccessor([ 'type' => 'foo', 'location' => 'bar' ]);

		$this->assertEquals(1, $resolver->produce($accessor));
	}

	public function testProduce_invalidAccessor() {
		$resolver = $this->_produceResolver();
		$accessor = $this->_produceAccessor([]);

		$this->expectException(IAccessorException::class);
		$this->expectExceptionMessage('ACC invalid key "type"');

		$resolver->produce($accessor);
	}
}
