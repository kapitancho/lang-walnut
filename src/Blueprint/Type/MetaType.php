<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;

interface MetaType extends Type {
	public MetaTypeValue $value { get; }
}