<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType as ResultTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

final readonly class ResultType implements ResultTypeInterface, SupertypeChecker, JsonSerializable {

    public function __construct(
        public Type $returnType,
        public Type $errorType
    ) {}

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure {
		$returnResult = $this->returnType->hydrate($request);
		if ($returnResult instanceof HydrationSuccess) {
			return $returnResult;
		}
		$errorResult = $this->errorType->hydrate($request);
		if ($errorResult instanceof HydrationSuccess) {
			return $request->ok(
				$request->valueRegistry->error(
					$errorResult->hydratedValue
				)
			);
		}
		return $request->withError(
			"Both return type and error type hydration failed.",
			$this
		)
			->mergeFailure($returnResult)
			->mergeFailure($errorResult);
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

	public function validate(ValidationRequest $request): ValidationResult {
		$result = $this->returnType->validate($request);
		return $this->errorType->validate($result);
	}

	public function jsonSerialize(): array {
		return ['type' => 'Result', 'returnType' => $this->returnType, 'errorType' => $this->errorType];
	}
}