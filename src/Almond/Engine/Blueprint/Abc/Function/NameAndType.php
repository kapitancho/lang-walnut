<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Abc\Function;

use Stringable;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;

interface NameAndType extends Stringable {
    public Type $type { get; }
	public VariableName|null $name { get; }
}