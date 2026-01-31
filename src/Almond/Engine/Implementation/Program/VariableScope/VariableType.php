<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Program\VariableScope;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableType as VariableTypeInterface;

final readonly class VariableType implements VariableTypeInterface {
	public function __construct(
		public VariableName $name,
		public Type $type,
	) {}
}