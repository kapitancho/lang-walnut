<?php

namespace Walnut\Lang\Implementation\Code\Scope;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Code\Scope\UnknownContextVariable;
use Walnut\Lang\Blueprint\Code\Scope\UnknownVariable;
use Walnut\Lang\Blueprint\Code\Scope\VariableScope as VariableScopeInterface;
use Walnut\Lang\Blueprint\Code\Scope\VariableValueScope as VariableValueScopeInterface;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class VariableValueScope implements VariableValueScopeInterface, JsonSerializable {
	/** @param array<string, TypedValue> $variables */
	public function __construct(
		private array $variables
	) {}

	/** @return string[] */
	public function variables(): array {
		return array_keys($this->variables);
	}

	public function findTypeOf(VariableNameIdentifier $variableName): Type|UnknownVariable {
		$var = $this->findTypedValueOf($variableName);
		return $var instanceof UnknownVariable ? $var : $var->type;
	}

	public function typeOf(VariableNameIdentifier $variableName): Type {
		return $this->typedValueOf($variableName)->type;
	}

	public function withAddedVariableType(
		VariableNameIdentifier $variableName,
		Type $variableType
	): VariableScopeInterface {
		return new VariableScope([
			...array_map(static fn(TypedValue $typedValue): Type => $typedValue->type, $this->variables),
			$variableName->identifier => $variableType
		]);
	}

	public function findTypedValueOf(VariableNameIdentifier $variableName): TypedValue|UnknownVariable {
		return $this->variables[$variableName->identifier] ?? UnknownVariable::value;
	}

	public function findValueOf(VariableNameIdentifier $variableName): Value|UnknownVariable {
		$var = $this->findTypedValueOf($variableName);
		return $var instanceof UnknownVariable ? $var : $var->value;
	}

	public function typedValueOf(VariableNameIdentifier $variableName): TypedValue {
		return $this->variables[$variableName->identifier] ??
			UnknownContextVariable::withName($variableName);
	}

	public function valueOf(VariableNameIdentifier $variableName): Value {
		return $this->typedValueOf($variableName)->value;
	}

	public function withAddedVariableValue(
		VariableNameIdentifier $variableName,
		TypedValue $value
	): VariableValueScopeInterface {
		return new self([
			...$this->variables,
			$variableName->identifier => $value
		]);
	}

	/** @return iterable<VariableNameIdentifier, TypedValue> */
	public function allTypedValues(): iterable {
		foreach($this->variables as $name => $typedValue) {
			yield new VariableNameIdentifier($name) => $typedValue;
		}
	}

	/** @return iterable<VariableNameIdentifier, Type> */
	public function allTypes(): iterable {
		foreach($this->allTypedValues() as $name => $typedValue) {
			yield $name => $typedValue->type;
		}
	}

	/** @return iterable<VariableNameIdentifier, Value> */
	public function allValues(): iterable {
		foreach($this->allTypedValues() as $name => $typedValue) {
			yield $name => $typedValue->value;
		}
	}

	public static function empty(): VariableValueScopeInterface {
		return new self([]);
	}

	public function jsonSerialize(): array {
		return array_map(static fn(TypedValue $typedValue): array => [
			'type' => $typedValue->type,
			'value' => $typedValue->value
		], $this->variables);
	}
}