<?php

namespace Walnut\Lang\NativeCode\DatabaseConnector;

use BcMath\Number;
use PDO;
use PDOException;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\SealedValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Query implements NativeMethod {
	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		if ($targetType instanceof SealedType && $targetType->name->equals(
			new TypeNameIdentifier('DatabaseConnector')
		)) {
			if ($parameterType->isSubtypeOf(
				$this->context->typeRegistry->withName(
					new TypeNameIdentifier('DatabaseQueryCommand')
				)
			)) {
				return $this->context->typeRegistry->result(
					$this->context->typeRegistry->withName(
						new TypeNameIdentifier('DatabaseQueryResult')
					),
					$this->context->typeRegistry->withName(
						new TypeNameIdentifier('DatabaseQueryFailure')
					)
				);
			}
			// @codeCoverageIgnoreStart
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
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
				$this->context->typeRegistry->withName(
					new TypeNameIdentifier('DatabaseQueryCommand')
				)
			)) {
				$dsn = $targetValue->value->valueOf('connection')
					->baseValue->values()['dsn']->literalValue;
				try {
					$pdo = new PDO($dsn);
					$stmt = $pdo->prepare($parameterValue->values['query']->literalValue);
					$stmt->execute(array_map(static fn(Value $value): string|int|null =>
						$value->literalValue instanceof Number ? (string)$value->literalValue : $value->literalValue,
						 $parameterValue->values['boundParameters']->values()
					));
					$result = [];
					foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
						$result[] = $this->context->valueRegistry->record(
							array_map(
								fn(string|float|int|null $value): Value => match(gettype($value)) {
									'string' => $this->context->valueRegistry->string($value),
									'double' => $this->context->valueRegistry->real($value),
									'integer' => $this->context->valueRegistry->integer($value),
									'NULL' => $this->context->valueRegistry->null,
									default => throw new ExecutionException("Invalid value type")
								},
								$row
							)
						);
					}
					return new TypedValue(
						$this->context->typeRegistry->result(
							$this->context->typeRegistry->withName(
								new TypeNameIdentifier('DatabaseQueryResult')
							),
							$this->context->typeRegistry->withName(
								new TypeNameIdentifier('DatabaseQueryFailure')
							)
						),
						$this->context->valueRegistry->tuple($result)
					);
				} catch (PDOException $ex) {
					return new TypedValue(
						$this->context->typeRegistry->result(
							$this->context->typeRegistry->nothing,
							$this->context->typeRegistry->withName(
								new TypeNameIdentifier('DatabaseQueryFailure')
							)
						),
						$this->context->valueRegistry->error(
							$this->context->valueRegistry->sealedValue(
								new TypeNameIdentifier('DatabaseQueryFailure'),
								$this->context->valueRegistry->record([
									'query' => $parameterValue->values['query'],
									'boundParameters' => $parameterValue->values['boundParameters'],
									'error' => $this->context->valueRegistry->string($ex->getMessage())
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