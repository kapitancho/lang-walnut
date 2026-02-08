<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Method;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Method;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;

final readonly class UnionMethodCall implements Method {

	/**
	 * @param list<array{Type, Method}> $methods
	 */
	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private array $methods
	) {}

	public function validate(
		Type $targetType,
		Type $parameterType,
		mixed $origin
	): ValidationSuccess|ValidationFailure {
		$types = [];
		$failure = null;
		foreach($this->methods as [$methodType, $method]) {
			$tResult = $method->validate($methodType, $parameterType, $origin);
			if ($tResult instanceof ValidationFailure) {
				$failure = $failure ? $failure->mergeWith($tResult) : $tResult;
			} else {
				$types[] = $tResult->type;
			}
		}
		return $failure ?? $this->validationFactory->validationSuccess(
			$this->typeRegistry->union($types)
		);
	}

	// It should never happen
	// @codeCoverageIgnoreStart
	public function execute(
		Value $target,
		Value $parameter,
	): Value {
		foreach($this->methods as [$methodType, $method]) {
            if ($target->type->isSubtypeOf($methodType)) {
                return $method->execute($target, $parameter);
            }
		}
		throw new ExecutionException("Union method call is not executable");
	}
	// @codeCoverageIgnoreEnd
}