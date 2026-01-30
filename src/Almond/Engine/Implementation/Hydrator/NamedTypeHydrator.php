<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Hydrator;

use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationFailure as HydrationFailureInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationRequest as HydrationRequestInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationSuccess as HydrationSuccessInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\NamedTypeHydrator as NamedTypeHydratorInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Method\MethodContext;
use Walnut\Lang\Almond\Engine\Blueprint\Type\NamedType;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Value\ErrorValue;

final readonly class NamedTypeHydrator implements NamedTypeHydratorInterface {
	public function __construct(
		private MethodContext $methodContext,
	) {}

	public function tryHydrateByName(
		NamedType $targetType,
		HydrationRequestInterface $request
	): null|HydrationSuccessInterface|HydrationFailureInterface {
		$value = $request->value;
		$canCall = $this->methodContext->validateCast(
			$value->type,
			$targetType->name,
			null
		);
		if ($canCall instanceof ValidationFailure) {
			return null;
		}
		$result = $this->methodContext->executeCast($value, $targetType->name);
		if ($result instanceof ErrorValue) {
			return $request->withError(
				sprintf(
					"Type %s hydration failed. Error: %s",
					$targetType->name,
					$result->errorValue
				),
				$targetType,
			);
		}
		return $request->ok($result);
	}


}