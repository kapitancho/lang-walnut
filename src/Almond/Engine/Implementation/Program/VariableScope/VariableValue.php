<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Program\VariableScope;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableType as VariableTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableValue as VariableValueInterface;

final readonly class VariableValue implements VariableTypeInterface, VariableValueInterface {
	public Type $type;
	public function __construct(
		public VariableName $name,
		public Value $value,
	) {
		$this->type = $value->type;
	}
}