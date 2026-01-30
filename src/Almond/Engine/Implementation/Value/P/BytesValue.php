<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Value\P;

use JsonSerializable;
use Walnut\Lang\Almond\AST\Blueprint\Parser\EscapeCharHandler;
use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Type\BytesType;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Blueprint\Value\BytesValue as BytesValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

final class BytesValue implements BytesValueInterface, JsonSerializable {

	public function __construct(
		private readonly TypeRegistry $typeRegistry,
		private readonly EscapeCharHandler $escapeCharHandler,
		public readonly string $literalValue
	) {}

	public BytesType $type {
		get => $this->typeRegistry->bytes(
			$l = strlen($this->literalValue),
			$l
		);
	}

	public function equals(Value $other): bool {
		return $other instanceof BytesValueInterface && $this->literalValue === $other->literalValue;
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
			'valueType' => 'Bytes',
			'value' => $this->literalValue
		];
	}
}
