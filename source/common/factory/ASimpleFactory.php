<?php

namespace eve\common\factory;

use eve\common\base\ArrayOperation;



abstract class ASimpleFactory
implements ISimpleFactory
{

	private $_base;


	public function __construct(IBaseFactory $base) {
		$this->_base = $base;
	}


	protected function _getConfigDefaults() : array {
		return [];
	}

	abstract protected function _produceInstance(IBaseFactory $base, array $config);


	public function produce(array& $config = []) {
		$defaults = $this->_getConfigDefaults();
		$settings = $this->_base->callMethod(ArrayOperation::class, 'merge', [$defaults, $config]);

		return $this->_produceInstance($this->_base, $settings);
	}
}
