<?php

namespace test\inject\resolve;

use eve\common\factory\ISimpleFactory;
use PHPUnit\Framework\TestCase;

use eve\common\IFactory;
use eve\common\factory\IAccessorFactory;
use eve\common\access\IItemAccessor;
use eve\common\access\TraversableAccessor;
use eve\common\access\exception\IAccessorException;
use eve\common\assembly\IAssemblyHost;
use eve\inject\IInjector;
use eve\inject\IInjectable;
use eve\inject\IInjectableFactory;
use eve\inject\resolve\IInjectorResolver;
use eve\inject\resolve\FactoryResolver;




final class FactoryResolverTest
extends TestCase
{

	private function _mockInterface(string $qname) {
		$ins = $this
			->getMockBuilder($qname)
			->getMock();

		return $ins;
	}

	private function _mockInjector(array $map = []) : IInjector {
		$ins = $this->_mockInterface(IInjector::class);

		$ins
			->method('produce')
			->with($this->isType('string'))
			->willReturnCallback(function(string $qname) use ($map) {
				return $map[$qname];
			});

		return $ins;
	}

	private function _mockAccessorFactory() {
		$ins = $this
			->getMockBuilder(IFactory::class)
			->setMethods([ 'produce', 'select' ])
			->getMock();

		$ins
			->method('produce')
			->with($this->isType('array'))
			->willReturnCallback(function(array $data) {
				return $this->_produceAccessor($data);
			});

		$ins
			->method('select')
			->with(
				$this->isInstanceOf(IItemAccessor::class),
				$this->isType('string')
			)
			->willReturnCallback(function(IItemAccessor $source, string $key) {
				return $this->_produceAccessor($source->getItem($key));
			});

		return $ins;
	}

	private function _mockDriverAssembly(IInjector $injector = null, ISimpleFactory $accessor = null) : IAssemblyHost {
		if (is_null($injector)) $injector = $this->_mockInjector();
		if (is_null($accessor)) $accessor = $this->_mockAccessorFactory();

		$ins = $this->_mockInterface(IAssemblyHost::class);

		$ins
			->method('getItem')
			->with($this->logicalOr(
				$this->equalTo('injector'),
				$this->equalto('accessorFactory')
			))
			->willReturnMap([
				[ 'injector', $injector ],
				[ 'accessorFactory', $accessor ]
			]);

		return $ins;
	}

	private function _mockFactory() : IInjectableFactory {
		$ins = $this->_mockInterface(IInjectableFactory::class);

		$ins
			->method('produce')
			->with($this->isInstanceOf(IItemAccessor::class))
			->willReturnArgument(0);

		return $ins;
	}

	private function _produceAccessor(array $data) {
		return new TraversableAccessor($data);
	}

	private function _produceResolver(IAssemblyHost $driver = null) {
		if (is_null($driver)) $driver = $this->_mockDriverAssembly();

		return new FactoryResolver($driver);
	}


	public function testInheritance() {
		$resolver = $this->_produceResolver();

		$this->assertInstanceOf(IInjectorResolver::class, $resolver);
		$this->assertInstanceOf(IInjectable::class, $resolver);
		$this->assertInstanceOf(IAccessorFactory::class, $resolver);
		$this->assertInstanceOf(IFactory::class, $resolver);
	}


	public function testDependencyConfig() {
		$driverAssembly = $this->_mockDriverAssembly();

		$this->assertEquals([[
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $driverAssembly
		]], FactoryResolver::getDependencyConfig($this->_produceAccessor([
			'driver' => $driverAssembly
		])));
	}


	public function testProduce() {
		$injector = $this->_mockInjector([
			'qname' => $this->_mockFactory()
		]);
		$assembly = $this->_mockDriverAssembly($injector);
		$resolver = $this->_produceResolver($assembly);
		$accessor = $this->_produceAccessor([
			'type' => 'factory',
			'factory' => 'qname',
			'config' => [
				'bar' => 'baz'
			]
		]);

		$resolved = $resolver->produce($accessor);

		$this->assertInstanceOf(IItemAccessor::class, $resolved);
		$this->assertTrue($resolved->hasKey('bar'));
		$this->assertEquals('baz', $resolved->getItem('bar'));
	}

	public function testProduce_noConfig() {
		$injector = $this->_mockInjector([
			'qname' => $this->_mockFactory()
		]);
		$assembly = $this->_mockDriverAssembly($injector);
		$resolver = $this->_produceResolver($assembly);
		$accessor = $this->_produceAccessor([
			'type' => 'factory',
			'factory' => 'qname'
		]);

		$resolved = $resolver->produce($accessor);

		$this->assertInstanceOf(IItemAccessor::class, $resolved);
		$this->assertEmpty($resolved->getProjection());
	}

	public function testProduce_invalidAccessor() {
		$injector = $this->_mockInjector([
			'qname' => $this->_mockFactory()
		]);
		$assembly = $this->_mockDriverAssembly($injector);
		$resolver = $this->_produceResolver($assembly);
		$accessor = $this->_produceAccessor([]);

		$this->expectException(IAccessorException::class);
		$this->expectExceptionMessage('ACC invalid key "factory"');

		$resolver->produce($accessor);
	}

	public function testProduce_invalidInterface() {
		$injector = $this->_mockInjector([
			'qname' => new \stdClass()
		]);
		$assembly = $this->_mockDriverAssembly($injector);
		$resolver = $this->_produceResolver($assembly);
		$accessor = $this->_produceAccessor([ 'factory' => 'qname' ]);

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('INJ not a factory "qname"');

		$resolver->produce($accessor);
	}
}
