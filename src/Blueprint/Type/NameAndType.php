<?php

namespace Walnut\Lang\Blueprint\Type;

use Stringable;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;

interface NameAndType extends Stringable {
    public Type $type { get; }
	public VariableNameIdentifier|null $name { get; }
}