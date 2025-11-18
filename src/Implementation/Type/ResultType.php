<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\ProxyNamedType;
use Walnut\Lang\Blueprint\Type\ResultType as ResultTypeInterface;
use Walnut\Lang\Blueprint\Type\SupertypeChecker;
use Walnut\Lang\Blueprint\Type\Type;

final class ResultType implements ResultTypeInterface, SupertypeChecker, JsonSerializable {

	private readonly Type $realReturnType;
	private readonly Type $realErrorType;

    public function __construct(
        private readonly Type $declaredReturnType,
        private readonly Type $declaredErrorType
    ) {}

	public Type $returnType {
		get => $this->realReturnType ??= $this->declaredReturnType instanceof ProxyNamedType ?
			$this->declaredReturnType->actualType : $this->declaredReturnType;
    }

	public Type $errorType {
		get => $this->realErrorType ??= $this->declaredErrorType instanceof ProxyNamedType ?
            $this->declaredErrorType->actualType : $this->declaredErrorType;
    }

    public function isSubtypeOf(Type $ofType): bool {
	    return match(true) {
			$ofType instanceof ResultTypeInterface =>
				$this->returnType->isSubtypeOf($ofType->returnType) &&
                $this->errorType->isSubtypeOf($ofType->errorType),
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
			default => false
		};
    }

    public function isSupertypeOf(Type $ofType): bool {
        return $ofType->isSubtypeOf($this->returnType);
    }

	public function __toString(): string {
		if ($this->returnType instanceof NothingType) {
			return sprintf(
				"Error<%s>",
				$this->errorType
			);
		}
		return sprintf(
			"Result<%s, %s>",
			$this->returnType,
            $this->errorType
		);
	}

	public function jsonSerialize(): array {
		return ['type' => 'Result', 'returnType' => $this->returnType, 'errorType' => $this->errorType];
	}
}