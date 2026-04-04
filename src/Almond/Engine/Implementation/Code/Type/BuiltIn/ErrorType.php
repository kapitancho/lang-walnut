<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AnyType as AnyTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ErrorType as ErrorTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType as NothingTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType as ResultTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

final readonly class ErrorType implements ErrorTypeInterface, JsonSerializable {

	public NothingTypeInterface $returnType;

    public function __construct(
        TypeRegistry $typeRegistry,
        public Type $errorType
    ) {
		$this->returnType = $typeRegistry->nothing;
    }

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure {
		$errorResult = $this->errorType->hydrate($request);
		if ($errorResult instanceof HydrationSuccess) {
			return $request->ok(
				$request->valueRegistry->error(
					$errorResult->hydratedValue
				)
			);
		}
		return $errorResult;
	}

	public function isSubtypeOf(Type $ofType): bool {
	    return match(true) {
			$ofType instanceof ResultTypeInterface =>
                $this->errorType->isSubtypeOf($ofType->errorType),
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
			default => false
		};
    }

	public function __toString(): string {
		return $this->errorType instanceof AnyTypeInterface ?
			'Error' : sprintf("Error<%s>", $this->errorType);
	}

	public function validate(ValidationRequest $request): ValidationResult {
		return $this->errorType->validate($request);
	}

	public function jsonSerialize(): array {
		return ['type' => 'Error', 'errorType' => $this->errorType];
	}
}