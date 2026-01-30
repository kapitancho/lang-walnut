<?php

namespace Walnut\Lang\Almond\Engine\Implementation\VariableScope;

use Walnut\Lang\Almond\Engine\Blueprint\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\VariableScope\VariableType as VariableTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\VariableScope\VariableValue as VariableValueInterface;

final readonly class VariableValue implements VariableTypeInterface, VariableValueInterface {
	public Type $type;
	public function __construct(
		public VariableName $name,
		public Value $value,
	) {
		$this->type = $value->type;
	}
}