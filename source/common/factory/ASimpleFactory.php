<?php

namespace eve\common\factory;

use eve\common\base\ArrayOperation;



abstract class ASimpleFactory
implements ISimpleFactory
{

	private $_core;


	public function __construct(ICoreFactory $core) {
		$this->_core = $core;
	}


	protected function _getConfigDefaults() : array {
		return [];
	}

	abstract protected function _produceInstance(ICoreFactory $core, array $config);


	public function produce(array& $config = []) {
		$defaults = $this->_getConfigDefaults();
		$settings = $this->_core->callMethod(ArrayOperation::class, 'merge', [$defaults, $config]);

		return $this->_produceInstance($this->_core, $settings);
	}
}
