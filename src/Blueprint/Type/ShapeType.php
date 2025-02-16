<?php

namespace Walnut\Lang\Blueprint\Type;

interface ShapeType extends Type {
	public Type $refType { get; }
}