<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Program\VariableScope;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableScope as VariableScopeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableType as VariableTypeInterface;

final readonly class VariableScope implements VariableScopeInterface {
	/** @var list<VariableTypeInterface> $types */
	public array $types;

	/** @param array<string, VariableTypeInterface> $typesMap */
	public function __construct(private array $typesMap) {
		$this->types = array_values($this->typesMap);
	}

	public function variables(): array {
		return array_keys($this->typesMap);
	}

	public function typeOf(VariableName $name): Type|null {
		return $this->typesMap[$name->identifier]?->type ?? null;
	}

	/**  @param iterable<VariableName, Type> $types */
	public function withAddedVariableTypes(iterable $types): VariableScope {
		$typesMap = $this->typesMap;
		foreach ($types as $name => $type) {
			$typesMap[$name->identifier] = $type;
		}
		return new self($typesMap);
	}

	public function withAddedVariableType(VariableName $name, Type $type): VariableScope {
		return new self([
			...$this->typesMap,
			$name->identifier => new VariableType($name, $type)
		]);
	}

	public function __toString(): string {
		return '[' . implode(', ', array_map(
			fn(VariableTypeInterface $variableType) =>
				sprintf('%s: %s', $variableType->name, $variableType->type),
			$this->typesMap
		)) . ']';
	}

}