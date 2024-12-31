<?php

namespace Walnut\Lang\Implementation\Value;

use JsonSerializable;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Value\ErrorValue as ErrorValueInterface;
use Walnut\Lang\Blueprint\Value\Value;

final class ErrorValue implements ErrorValueInterface, JsonSerializable {
    public function __construct(
        private readonly TypeRegistry $typeRegistry,
        public readonly Value $errorValue
    ) {}

	public ResultType $type {
        get => $this->typeRegistry->result(
            $this->typeRegistry->nothing,
            $this->errorValue->type
        );
    }

    public function equals(Value $other): bool {
        return $other instanceof self && $this->errorValue->equals($other->errorValue);
    }

    public function __toString(): string {
        return sprintf("@%s", $this->errorValue);
    }

	public function jsonSerialize(): array {
		return [
			'valueType' => 'Error',
			'errorValue' => $this->errorValue
		];
	}

}