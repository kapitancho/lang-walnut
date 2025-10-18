<?php

namespace Walnut\Lang\NativeCode\EventBus;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\SealedValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Fire implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		TypeInterface $targetType,
		TypeInterface $parameterType,
	): TypeInterface {
		if ($targetType instanceof SealedType && $targetType->name->equals(
			new TypeNameIdentifier('EventBus')
		)) {
			return $typeRegistry->result(
				$parameterType,
				$typeRegistry->withName(new TypeNameIdentifier('ExternalError'))
			);
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd

	}

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		$targetValue = $target;
		$parameterValue = $parameter;
		
		if ($targetValue instanceof SealedValue && $targetValue->type->name->equals(
			new TypeNameIdentifier('EventBus')
		)) {
			$listeners = $targetValue->value->values['listeners'] ?? null;
			if ($listeners instanceof TupleValue) {
				foreach($listeners->values as $listener) {
					if ($listener instanceof FunctionValue) {
						if ($parameterValue->type->isSubtypeOf($listener->type->parameterType)) {
							$result = $listener->execute($programRegistry->executionContext, $parameterValue);
							if ($result->type->isSubtypeOf(
								$programRegistry->typeRegistry->result(
									$programRegistry->typeRegistry->nothing,
									$programRegistry->typeRegistry->withName(new TypeNameIdentifier('ExternalError'))
								)
							)) {
								return $result;
							}
						}
					} else {
						throw new ExecutionException("Invalid listener");
					}
				}
				return $parameterValue;
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}