<?php

namespace eve\common\access;

use eve\common\IGenerateable;
use eve\common\projection\ICompactProjectable;



interface ITraversableAccessor
extends IItemAccessor, IGenerateable, ICompactProjectable
{

}
