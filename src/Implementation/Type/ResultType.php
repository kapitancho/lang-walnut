<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Type\ProxyNamedType;
use Walnut\Lang\Blueprint\Type\ResultType as ResultTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class ResultType implements ResultTypeInterface, SupertypeChecker, JsonSerializable {

	private Type $realReturnType;
	private Type $realErrorType;

    public function __construct(
        private Type $returnType,
        private Type $errorType
    ) {}

    public function returnType(): Type {
        return $this->realReturnType ??= $this->returnType instanceof ProxyNamedType ?
			$this->returnType->getActualType() : $this->returnType;
    }

    public function errorType(): Type {
        return $this->realErrorType ??= $this->errorType instanceof ProxyNamedType ?
            $this->errorType->getActualType() : $this->errorType;
    }

    public function isSubtypeOf(Type $ofType): bool {
	    return match(true) {
			$ofType instanceof ResultTypeInterface =>
				$this->returnType()->isSubtypeOf($ofType->returnType()) &&
                $this->errorType()->isSubtypeOf($ofType->errorType()),
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
			default => false
		};
    }

    public function isSupertypeOf(Type $ofType): bool {
        return $ofType->isSubtypeOf($this->returnType());
    }

	public function __toString(): string {
		return sprintf(
			"Result<%s, %s>",
			$this->returnType(),
            $this->errorType()
		);
	}

	public function jsonSerialize(): array {
		return ['type' => 'Result', 'returnType' => $this->returnType(), 'errorType' => $this->errorType()];
	}
}