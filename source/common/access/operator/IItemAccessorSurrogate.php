<?php

namespace eve\common\access\operator;

use eve\common\factory\ISimpleFactory;
use eve\common\projection\operator\IProjectableSurrogate;


interface IItemAccessorSurrogate
extends ISimpleFactory, IProjectableSurrogate, IItemAccessorComposition
{

}