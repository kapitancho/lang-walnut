<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Implementation\Type\SupertypeChecker;

interface ShapeType extends Type, SupertypeChecker {
	public Type $refType { get; }
}