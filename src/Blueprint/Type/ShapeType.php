<?php

namespace Walnut\Lang\Blueprint\Type;

interface ShapeType extends Type, SupertypeChecker {
	public Type $refType { get; }
}