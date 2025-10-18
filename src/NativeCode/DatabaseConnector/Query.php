<?php

namespace Walnut\Lang\NativeCode\DatabaseConnector;

use BcMath\Number;
use PDO;
use PDOException;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\SealedValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Query extends PdoMethod implements NativeMethod {
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
					$programRegistry->typeRegistry->withName(
						new TypeNameIdentifier('DatabaseQueryResult')
					),
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
		Value $target,
		Value $parameter
	): Value {
		$targetValue = $target;
		$parameterValue = $parameter;
		
		if ($targetValue instanceof SealedValue && $targetValue->type->name->equals(
			new TypeNameIdentifier('DatabaseConnector')
		)) {
			if ($parameterValue->type->isSubtypeOf(
				$programRegistry->typeRegistry->withName(
					new TypeNameIdentifier('DatabaseQueryCommand')
				)
			)) {
				$dsn = $targetValue->value->valueOf('connection')
					->value->values['dsn']->literalValue;
				try {
					$stmt = $this->getPdo($dsn)->prepare($parameterValue->values['query']->literalValue);
					$stmt->execute(array_map(fn(Value $value): string|int|null =>
					($v = $value)->literalValue instanceof Number ? (string)$v->literalValue : $v->literalValue,
						$parameterValue->values['boundParameters']->values
					));
					$result = [];
					foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
						$result[] = $programRegistry->valueRegistry->record(
							array_map(
								fn(string|float|int|null $value): Value => match(gettype($value)) {
									'string' => $programRegistry->valueRegistry->string($value),
									'double' => $programRegistry->valueRegistry->real($value),
									'integer' => $programRegistry->valueRegistry->integer($value),
									'NULL' => $programRegistry->valueRegistry->null,
									default => throw new ExecutionException("Invalid value type")
								},
								$row
							)
						);
					}

					return (
						$programRegistry->valueRegistry->tuple($result)
					);
				} catch (PDOException $ex) {
					return (
						$programRegistry->valueRegistry->error(
							$programRegistry->valueRegistry->dataValue(
								new TypeNameIdentifier('DatabaseQueryFailure'),
								$programRegistry->valueRegistry->record([
									'query' => $parameterValue->values['query'],
									'boundParameters' => $parameterValue->values['boundParameters'],
									'error' => $programRegistry->valueRegistry->string($ex->getMessage())
								])
							)
						)
					);
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