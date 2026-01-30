<?php

namespace Walnut\Lang\Almond\Engine\Implementation\VariableScope;

use Walnut\Lang\Almond\Engine\Blueprint\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\VariableScope\VariableType as VariableTypeInterface;

final readonly class VariableType implements VariableTypeInterface {
	public function __construct(
		public VariableName $name,
		public Type $type,
	) {}
}