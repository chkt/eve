<?php

namespace eve\inject\cache;

use eve\common\access\ITraversableAccessor;
use eve\common\factory\IBaseFactory;
use eve\inject\IInjectableIdentity;



class KeyEncoder
implements IKeyEncoder
{

	private $_baseFactory;


	public function __construct(IBaseFactory $base) {
		$this->_baseFactory = $base;
	}


	public function encode(string $qname, string $id) : string {
		return $qname . ':' . $id;
	}

	public function encodeIdentity(string $qname, ITraversableAccessor $config) : string {
		if (!$this->_baseFactory->hasInterface($qname, IInjectableIdentity::class)) throw new \ErrorException(sprintf('KEY cannot create key for "%s"', $qname));

		$id = $this->_baseFactory->callMethod($qname, 'getInstanceIdentity', [ $config ]);

		return $this->encode($qname, $id);
	}
}
