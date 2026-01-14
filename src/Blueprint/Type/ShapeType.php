<?php

namespace Walnut\Lang\Blueprint\Type;

interface ShapeType extends CompositeType {
	public Type $refType { get; }
}