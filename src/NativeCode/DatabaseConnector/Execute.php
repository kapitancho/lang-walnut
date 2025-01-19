<?php

namespace Walnut\Lang\NativeCode\DatabaseConnector;

use BcMath\Number;
use PDO;
use PDOException;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\SealedValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Execute implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		if ($targetType instanceof SealedType && $targetType->name->equals(
			new TypeNameIdentifier('DatabaseConnector')
		)) {
			if ($parameterType->isSubtypeOf(
				$programRegistry->typeRegistry->withName(
					new TypeNameIdentifier('DatabaseQueryCommand')
				)
			)) {
				return $programRegistry->typeRegistry->result(
					$programRegistry->typeRegistry->integer(0),
					$programRegistry->typeRegistry->withName(
						new TypeNameIdentifier('DatabaseQueryFailure')
					)
				);
			}
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		ProgramRegistry $programRegistry,
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;
		$parameterValue = $parameter->value;
		
		$targetValue = $this->toBaseValue($targetValue);
		if ($targetValue instanceof SealedValue && $targetValue->type->name->equals(
			new TypeNameIdentifier('DatabaseConnector')
		)) {
			if ($parameterValue->type->isSubtypeOf(
				$programRegistry->typeRegistry->withName(
					new TypeNameIdentifier('DatabaseQueryCommand')
				)
			)) {
				$dsn = $targetValue->value->valueOf('connection')
					->baseValue->values['dsn']->literalValue;
				try {
					$pdo = new PDO($dsn);
					$stmt = $pdo->prepare($parameterValue->values['query']->literalValue);
					$stmt->execute(array_map(static fn(Value $value): string|float|int|null =>
						$value->literalValue instanceof Number ? (string)$value->literalValue : $value->literalValue,
						$parameterValue->values['boundParameters']->values
					));
					$rowCount = $stmt->rowCount();
					return new TypedValue(
						$programRegistry->typeRegistry->result(
							$programRegistry->typeRegistry->integer(0),
							$programRegistry->typeRegistry->withName(
								new TypeNameIdentifier('DatabaseQueryFailure')
							)
						),
						$programRegistry->valueRegistry->integer($rowCount)
					);
				} catch (PDOException $ex) {
					return TypedValue::forValue($programRegistry->valueRegistry->error(
						$programRegistry->valueRegistry->sealedValue(
							new TypeNameIdentifier('DatabaseQueryFailure'),
							$programRegistry->valueRegistry->record([
								'query' => $parameterValue->values['query'],
								'boundParameters' => $parameterValue->values['boundParameters'],
								'error' => $programRegistry->valueRegistry->string($ex->getMessage())
							])
						)
					));
				}
			}
			// @codeCoverageIgnoreStart
			throw new AnalyserException("Invalid parameter value");
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}