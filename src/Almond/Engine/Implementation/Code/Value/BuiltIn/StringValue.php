<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Value\BuiltIn;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue as StringValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\EscapeCharHandler;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

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