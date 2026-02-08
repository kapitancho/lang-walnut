<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Feature\Hydrator;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\MethodContext;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\NamedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationFailure as HydrationFailureInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationRequest as HydrationRequestInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationSuccess as HydrationSuccessInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\NamedTypeHydrator as NamedTypeHydratorInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;

final readonly class NamedTypeHydrator implements NamedTypeHydratorInterface {
	public function __construct(
		private MethodContext $methodContext,
	) {}

	public function tryHydrateByName(
		NamedType $targetType,
		HydrationRequestInterface $request
	): null|HydrationSuccessInterface|HydrationFailureInterface {
		$jsonValueType = $request->typeRegistry->core->jsonValue;
		$value = $request->value;
		$canCall = $this->methodContext->validateCast(
			$jsonValueType,
			$targetType->name,
			null
		);
		if ($canCall instanceof ValidationFailure) {
			return null;
		}
		$result = $this->methodContext->methodForType(
			$jsonValueType,
			new MethodName('as' . $targetType->name)
		)->execute($value, $request->valueRegistry->null);
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