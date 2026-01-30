<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Type;

interface TypeType extends Type {
	public Type $refType { get; }
}