<?php

namespace test\inject;

use PHPUnit\Framework\TestCase;

use eve\common\factory\ISimpleFactory;
use eve\common\factory\ICoreFactory;
use eve\common\access\IItemMutator;
use eve\common\access\TraversableAccessor;
use eve\common\access\TraversableMutator;
use eve\inject\IInjectable;
use eve\inject\IInjectableIdentity;
use eve\inject\Injector;
use eve\inject\IdentityInjector;
use eve\inject\cache\IKeyEncoder;
use eve\driver\IInjectorDriver;



final class IdentityInjectorTest
extends TestCase
{

	private function _mockFactory() : ICoreFactory {
		$ins = $this
			->getMockBuilder(ICoreFactory::class)
			->getMock();

		$ins
			->expects($this->any())
			->method('hasInterface')
			->with(
				$this->logicalAnd(
					$this->isType('string'),
					$this->logicalNot($this->isEmpty())
				),
				$this->isType('string')
			)
			->willReturnCallback(function(string $qname, string $iname) : bool {
				switch ($qname) {
					case IInjectableIdentity::class : return $iname === IInjectableIdentity::class || $iname === IInjectable::class;
					case IInjectable::class : return $iname === $qname;
					default : return false;
				}
			});

		$ins
			->expects($this->any())
			->method('callMethod')
			->with(
				$this->isType('string'),
				$this->logicalOr(
					$this->equalTo('getDependencyConfig'),
					$this->equalTo('getInstanceIdentity')
				),
				$this->isType('array')
			)
			->willReturnCallback(function(string $qname, string $method, array $args) {
				switch ($method) {
					case 'getDependencyConfig' : return [];
					case 'getInstanceIdentity' : return implode('-', $args[0]->getProjection());
					default : throw new \ErrorException();
				}
			});

		$ins
			->expects($this->any())
			->method('newInstance')
			->with(
				$this->logicalAnd(
					$this->isType('string'),
					$this->logicalNot($this->isEmpty())
				),
				$this->isType('array')
			)
			->willReturnCallback(function(string $qname, array $args) {
				return $this
					->getMockBuilder($qname)
					->getMock();
			});

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

	private function _mockKeyEncoder() {
		$ins = $this
			->getMockBuilder(IKeyEncoder::class)
			->getMock();

		$ins
			->method('encode')
			->with(
				$this->isType('string'),
				$this->isType('string')
			)
			->willReturnCallback(function(string $qname, string $id) {
				return $qname . ':' . $id;
			});

		return $ins;
}

	private function _mockDriver(IKeyEncoder $encoder = null, IItemMutator $cache = null) : IInjectorDriver {
		$factory = $this->_mockFactory();
		$accessor = $this->_mockAccessorFactory();

		if (is_null($encoder)) $encoder = $this->_mockKeyEncoder();
		if (is_null($cache)) $cache = $this->_produceCache();

		$ins = $this
			->getMockBuilder(IInjectorDriver::class)
			->getMock();

		$ins
			->expects($this->exactly(2))
			->method('getCoreFactory')
			->with()
			->willReturn($factory);

		$ins
			->expects($this->exactly(2))
			->method('getAccessorFactory')
			->with()
			->willReturn($accessor);

		$ins
			->expects($this->once())
			->method('getKeyEncoder')
			->willReturn($encoder);

		$ins
			->expects($this->once())
			->method('getInstanceCache')
			->with()
			->willReturn($cache);

		return $ins;
	}


	private function _produceCache() {
		$data = [];

		return new TraversableMutator($data);
	}


	private function _produceInjector(IInjectorDriver $driver = null) {
		if (is_null($driver)) $driver = $this->_mockDriver();

		return new IdentityInjector($driver, []);
	}


	public function testInheritance() {
		$injector = $this->_produceInjector();

		$this->assertInstanceOf(Injector::class, $injector);
	}

	public function testProduce() {
		$encoder = $this->_mockKeyEncoder();
		$cache = $this->_produceCache();
		$driver = $this->_mockDriver($encoder, $cache);
		$injector = $this->_produceInjector($driver);

		$a =  $injector->produce(IInjectableIdentity::class, [
			'foo',
			'bar'
		]);

		$this->assertInstanceOf(IInjectableIdentity::class, $a);
		$this->assertTrue($cache->hasKey(IInjectableIdentity::class . ':foo-bar'));
		$this->assertSame($a, $cache->getItem(IInjectableIdentity::class . ':foo-bar'));

		$b = $injector->produce(IInjectableIdentity::class, [
			'foo',
			'bar'
		]);

		$this->assertSame($a, $b);

		$c = $injector->produce(IInjectableIdentity::class, [
			'foo',
			'baz'
		]);

		$this->assertInstanceOf(IInjectableIdentity::class, $c);
		$this->assertNotSame($c, $a);
		$this->assertTrue($cache->hasKey(IInjectableIdentity::class . ':foo-baz'));
		$this->assertSame($c, $cache->getItem(IInjectableIdentity::class . ':foo-baz'));
	}

	public function testProduce_noIdentity() {
		$encoder = $this->_mockKeyEncoder();
		$cache = $this->_produceCache();
		$driver = $this->_mockDriver($encoder, $cache);
		$injector = $this->_produceInjector($driver);

		$a = $injector->produce(IInjectable::class, [
			'foo',
			'bar'
		]);

		$this->assertInstanceOf(IInjectable::class, $a);
		$this->assertFalse($cache->hasKey(IInjectable::class . ':foo-bar'));

		$b = $injector->produce(IInjectable::class, [
			'foo',
			'bar'
		]);

		$this->assertInstanceOf(IInjectable::class, $b);
		$this->assertNotSame($a, $b);
	}
}
