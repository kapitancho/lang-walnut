<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Function\UserlandFunction;

interface SealedType extends NamedType {
	public Type $valueType { get; }
	public UserlandFunction|null $validator { get; }
}