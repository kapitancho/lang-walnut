<?php

namespace Walnut\Lang\Almond\Engine\Implementation\VariableScope;

use Walnut\Lang\Almond\Engine\Blueprint\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\VariableScope\VariableValueScope as VariableValueScopeInterface;

final readonly class VariableValueScope implements VariableValueScopeInterface {
	/** @var list<VariableValue> $values */
	public array $values;

	/** @var list<VariableType> $types */
	public array $types;

	/** @param array<string, VariableValue> $valuesMap */
	public function __construct(private array $valuesMap) {
		$this->types = $this->values = array_values($this->valuesMap);
	}

	/** @return list<string> */
	public function variables(): array {
		return array_keys($this->valuesMap);
	}

	public function typeOf(VariableName $name): Type|null {
		return $this->valuesMap[$name->identifier]?->type ?? null;
	}

	public function valueOf(VariableName $name): Value|null {
		return $this->valuesMap[$name->identifier]?->value ?? null;
	}

	/**  @param iterable<VariableName, Type> $types */
	public function withAddedVariableTypes(iterable $types): VariableScope {
		$typesMap = $this->valuesMap;
		foreach ($types as $name => $type) {
			$typesMap[$name->identifier] = new VariableType($name, $type);
		}
		return new VariableScope($typesMap);
	}

	public function withAddedVariableType(VariableName $name, Type $type): VariableScope {
		return new VariableScope([
			...$this->valuesMap,
			$name->identifier => new VariableType($name, $type)
		]);
	}

	/**  @param iterable<VariableName, Value> $values */
	public function withAddedVariableValues(iterable $values): VariableValueScope {
		$valuesMap = $this->valuesMap;
		foreach ($values as $name => $value) {
			$valuesMap[$name->identifier] = new VariableValue($name, $value);
		}
		return new self($valuesMap);
	}

	public function withAddedVariableValue(VariableName $name, Value $value): VariableValueScope {
		return new self([
			...$this->valuesMap,
			$name->identifier => new VariableValue($name, $value)
		]);
	}

	public function __toString(): string {
		return '[' . implode(', ', array_map(
			fn(VariableValue $variableValue) =>
				sprintf('%s: %s = %s', $variableValue->name, $variableValue->type, $variableValue->value),
			$this->valuesMap
		)) . ']';
	}

}