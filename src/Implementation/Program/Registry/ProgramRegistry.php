<?php

namespace Walnut\Lang\Implementation\Program\Registry;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Scope\VariableValueScope;
use Walnut\Lang\Blueprint\Program\Builder\CustomMethodRegistryBuilder;
use Walnut\Lang\Blueprint\Program\Registry\ExpressionRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry as ProgramRegistryInterface;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Implementation\Program\Builder\ScopeBuilder;

final class ProgramRegistry implements ProgramRegistryInterface, JsonSerializable {
	public function __construct(
		public readonly TypeRegistry $typeRegistry,
		public readonly ValueRegistry $valueRegistry,
		public readonly ExpressionRegistry $expressionRegistry,
		private readonly ScopeBuilder $globalScopeBuilder,
		private readonly CustomMethodRegistryBuilder $customMethodRegistryBuilder
	) {}

	public VariableValueScope $globalScope {
		get {
			return $this->globalScopeBuilder->build();
		}
	}

	public function jsonSerialize(): array {
		return [
			'typeRegistry' => $this->typeRegistry,
			'variables' => $this->globalScope,
			'customMethods' => $this->customMethodRegistryBuilder
		];
	}

	public function __toString(): string {
		$result = [];
		foreach($this->globalScope->allTypedValues() as $variableName => $typedValue) {
			$result[] = sprintf("%s: type: %s, value: %s", $variableName, $typedValue->type, $typedValue->value);
		}
		return sprintf("Variables: \n%s", implode(PHP_EOL, $result));
	}
}