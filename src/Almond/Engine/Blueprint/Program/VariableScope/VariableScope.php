<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\VariableName;

interface VariableScope {
	/** @var list<VariableType> $types */
	public array $types { get; }

	/** @return list<string> */
	public function variables(): array;

	public function typeOf(VariableName $name): Type|null;

	/**  @param iterable<VariableName, Type> $types */
	public function withAddedVariableTypes(iterable $types): VariableScope;
	public function withAddedVariableType(VariableName $name, Type $type): VariableScope;
}