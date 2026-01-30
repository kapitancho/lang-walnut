<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Value\P;

use JsonSerializable;
use Walnut\Lang\Almond\AST\Blueprint\Parser\EscapeCharHandler;
use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Blueprint\Value\StringValue as StringValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

final class StringValue implements StringValueInterface, JsonSerializable {

    public function __construct(
		private readonly TypeRegistry $typeRegistry,
		private readonly EscapeCharHandler $escapeCharHandler,
		public readonly string $literalValue
    ) {}

	public StringSubsetType $type {
		get => $this->typeRegistry->stringSubset([$this->literalValue]);
	}

	public function equals(Value $other): bool {
		return $other instanceof StringValueInterface && $this->literalValue === $other->literalValue;
	}


	public function validate(ValidationRequest $request): ValidationResult {
		return $request->ok();
	}

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		return $dependencyContext;
	}

	public function __toString(): string {
		return $this->escapeCharHandler->escape($this->literalValue);
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'String',
			'value' => $this->literalValue
		];
	}
}