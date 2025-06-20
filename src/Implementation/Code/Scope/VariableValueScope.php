<?php

namespace Walnut\Lang\Implementation\Code\Scope;

use Walnut\Lang\Blueprint\Code\Scope\UnknownContextVariable;
use Walnut\Lang\Blueprint\Code\Scope\UnknownVariable;
use Walnut\Lang\Blueprint\Code\Scope\VariableScope as VariableScopeInterface;
use Walnut\Lang\Blueprint\Code\Scope\VariableValueScope as VariableValueScopeInterface;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class VariableValueScope implements VariableValueScopeInterface {
	/** @param array<string, Value> $variables */
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
			...array_map(static fn(Value $typedValue): Type => $typedValue->type, $this->variables),
			$variableName->identifier => $variableType
		]);
	}

	public function findTypedValueOf(VariableNameIdentifier $variableName): Value|UnknownVariable {
		return $this->variables[$variableName->identifier] ?? UnknownVariable::value;
	}

	public function findValueOf(VariableNameIdentifier $variableName): Value|UnknownVariable {
		$var = $this->findTypedValueOf($variableName);
		return $var instanceof UnknownVariable ? $var : $var;
	}

	public function typedValueOf(VariableNameIdentifier $variableName): Value {
		return $this->variables[$variableName->identifier] ??
			UnknownContextVariable::withName($variableName);
	}

	public function valueOf(VariableNameIdentifier $variableName): Value {
		return $this->typedValueOf($variableName);
	}

	public function withAddedVariableValue(
		VariableNameIdentifier $variableName,
		Value $value
	): VariableValueScopeInterface {
		return new self([
			...$this->variables,
			$variableName->identifier => $value
		]);
	}

	/** @return iterable<VariableNameIdentifier, Value> */
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
			yield $name => $typedValue;
		}
	}

	public static function empty(): VariableValueScopeInterface {
		return new self([]);
	}
}