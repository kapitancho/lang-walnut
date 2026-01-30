<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Type;

interface ShapeType extends Type {
	public Type $refType { get; }
}