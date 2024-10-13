<?php

namespace Walnut\Lang\Implementation\Code\Scope;

use Walnut\Lang\Blueprint\Code\Scope\UnknownContextVariable;
use Walnut\Lang\Blueprint\Code\Scope\UnknownVariable;
use Walnut\Lang\Blueprint\Code\Scope\VariableScope as VariableScopeInterface;
use Walnut\Lang\Blueprint\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class VariableScope implements VariableScopeInterface {
	/** @param array<string, Type> $variables */
	public function __construct(
		private array $variables
	) {}

	/** @return string[] */
	public function variables(): array {
		return array_keys($this->variables);
	}

	public function findTypeOf(VariableNameIdentifier $variableName): Type|UnknownVariable {
		return $this->variables[$variableName->identifier] ??
			UnknownVariable::value;
	}

	public function typeOf(VariableNameIdentifier $variableName): Type {
		return $this->variables[$variableName->identifier] ??
			UnknownContextVariable::withName($variableName);
	}

	public function withAddedVariableType(VariableNameIdentifier $variableName, Type $variableType): VariableScopeInterface {
		return new self([
			...$this->variables,
			$variableName->identifier => $variableType
		]);
	}

	/** @return iterable<VariableNameIdentifier, Type> */
	public function allTypes(): iterable {
		foreach($this->variables as $name => $type) {
			yield new VariableNameIdentifier($name) => $type;
		}
	}

	public static function empty(): VariableScopeInterface {
		return new self([]);
	}
}