<?php

namespace test\driver;

use PHPUnit\Framework\TestCase;

use eve\common\factory\ISimpleFactory;
use eve\common\factory\ICoreFactory;
use eve\common\factory\ASimpleFactory;
use eve\common\access\ITraversableAccessor;
use eve\common\access\IItemMutator;
use eve\common\access\TraversableAccessor;
use eve\entity\IEntityParser;
use eve\inject\IInjector;
use eve\provide\ILocator;
use eve\driver\InjectorDriver;
use eve\driver\InjectorDriverFactory;



final class InjectorDriverFactoryTest
extends TestCase
{

	private function _mockInterface(string $qname, array $args = []) {
		$ins = $this
			->getMockBuilder($qname)
			->getMock();

		foreach ($args as $key => $value) {
			$prop = (is_numeric($key) ? 'p' : '') . $key;

			$ins->$prop = $value;
		}

		return $ins;
	}


	private function _mockAccessorFactory() : ISimpleFactory {
		$ins = $this
			->getMockBuilder(ISimpleFactory::class)
			->getMock();

		$ins
			->expects($this->any())
			->method('produce')
			->with($this->isType('array'))
			->willReturnCallback(function(array& $data) {
				return new TraversableAccessor($data);
			});

		return $ins;
	}


	private function _produceDriverFactory(ICoreFactory $core) {
		return new InjectorDriverFactory($core);
	}


	public function testInheritance() {
		$core = $this->_mockInterface(ICoreFactory::class);
		$fab = $this->_produceDriverFactory($core);

		$this->assertInstanceOf(ASimpleFactory::class, $fab);
	}

	public function testProduce_names() {
		$core = $this
			->getMockBuilder(ICoreFactory::class)
			->getMock();

		$core
			->expects($this->any())
			->method('newInstance')
			->with(
				$this->isType('string'),
				$this->logicalOr(
					$this->isType('array'),
					$this->isNull()
				)
			)
			->willReturnCallback(function(string $qname, array $args = []) {
				if ($qname === InjectorDriver::class) return new InjectorDriver(...$args);
				else if ($qname === ISimpleFactory::class) return $this->_mockAccessorFactory();
				else if ($qname === IInjector::class) {
					$injector = $this->_mockInterface(IInjector::class, $args);

					$injector
						->expects($this->once())
						->method('produce')
						->with(
							$this->equalTo(ILocator::class),
							$this->isType('array')
						)
						->willReturnCallback(function($qname, array $args = []) {
							return $this->_mockInterface($qname, $args);
						});

					return $injector;
				}
				else return $this->_mockInterface($qname, $args);
			});

		$fab = $this->_produceDriverFactory($core);

		$config = [
			'accessorFactoryName' => ISimpleFactory::class,
			'instanceCacheName' => IItemMutator::class,
			'entityParserName' => IEntityParser::class,
			'injectorName' => IInjector::class,
			'locatorName' => ILocator::class,
			'providers' => [
				'foo' => 'bar',
				'baz' => 'quux'
			]
		];

		$driver = $fab->produce($config);

		$this->assertInstanceOf(InjectorDriver::class, $driver);
		$this->assertInstanceOf(ISimpleFactory::class, $driver->getAccessorFactory());
		$this->assertInstanceOf(IItemMutator::class, $driver->getInstanceCache());
		$this->assertInstanceOf(IEntityParser::class, $driver->getEntityParser());
		$this->assertInstanceOf(IInjector::class, $driver->getInjector());
		$this->assertSame($driver, $driver->getInjector()->p0);
		$this->assertInternalType('array', $driver->getInjector()->p1);
		$this->assertInstanceOf(ILocator::class, $driver->getLocator());
		$this->assertSame($driver, $driver->getLocator()->driver);
		$this->assertSame($config['providers'], $driver->getLocator()->providerNames);
	}

	public function testProduce_instances() {
		$core = $this
			->getMockBuilder(ICoreFactory::class)
			->getMock();

		$core
			->expects($this->once())
			->method('newInstance')
			->with($this->equalTo(InjectorDriver::class))
			->willReturnCallback(function(string $qname, array $args) {
				return new InjectorDriver(...$args);
			});

		$accessor = $this
			->getMockBuilder(ISimpleFactory::class)
			->getMock();

		$accessor
			->expects($this->any())
			->method('produce')
			->with($this->isType('array'))
			->willReturnCallback(function(array& $data) {
				return new TraversableAccessor($data);
			});

		$cache = $this->_mockInterface(IItemMutator::class);
		$parser = $this->_mockInterface(IEntityParser::class);
		$injector = $this->_mockInterface(IInjector::class);
		$locator = $this->_mockInterface(ILocator::class);
		$fab = $this->_produceDriverFactory($core);

		$config = [
			'accessorFactory' => $accessor,
			'instanceCache' => $cache,
			'entityParser' => $parser,
			'injector' => $injector,
			'locator' => $locator
		];

		$driver = $fab->produce($config);

		$this->assertInstanceOf(InjectorDriver::class, $driver);
		$this->assertSame($accessor, $driver->getAccessorFactory());
		$this->assertSame($cache, $driver->getInstanceCache());
		$this->assertSame($parser, $driver->getEntityParser());
		$this->assertSame($injector, $driver->getInjector());
		$this->assertSame($locator, $driver->getLocator());
	}
}
