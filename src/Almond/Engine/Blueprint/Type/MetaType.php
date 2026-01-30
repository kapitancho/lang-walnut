<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Type;

interface MetaType extends Type {
	public MetaTypeValue $value { get; }
}