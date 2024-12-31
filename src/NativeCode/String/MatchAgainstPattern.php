<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class MatchAgainstPattern implements NativeMethod {
	use BaseType;

	private const string ROUTE_PATTERN_MATCH = '#\{([\w\_]+)\}#';
	private const string ROUTE_PATTERN_REPLACE = '#\{[\w\_]+\}#';
	private const string REPLACE_PATTERN = '(.+?)';

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof StringType || $targetType instanceof StringSubsetType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof StringType || $parameterType instanceof StringSubsetType) {
				return $this->context->typeRegistry->union([
					$this->context->typeRegistry->map(
						$this->context->typeRegistry->string(),
					),
					$this->context->typeRegistry->false
				]);
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
		$parameterValue = $this->toBaseValue($parameterValue);
		if ($targetValue instanceof StringValue) {
			if ($parameterValue instanceof StringValue) {
				$target = $targetValue->literalValue;
				$path = $parameterValue->literalValue;

				if (preg_match_all(self::ROUTE_PATTERN_MATCH, $path, $matches)) {
					$pathArgs = $matches[1] ?? [];
					$path = '^' . preg_replace(self::ROUTE_PATTERN_REPLACE, self::REPLACE_PATTERN, $path) . '$';
				} else {
					$pathArgs = null;
					$path = '^' . $path . '$';
				}
				$path = strtolower($path);
				if (!preg_match('#' . $path . '#', $target, $matches)) {
					return TypedValue::forValue($this->context->valueRegistry->false);
				}
				return TypedValue::forValue(is_array($pathArgs) ?
					$this->context->valueRegistry->record(
						array_map(fn($value) =>
							$this->context->valueRegistry->string($value),
							array_combine(
								$pathArgs,
								array_slice($matches, 1)
							)
						)
					) :
					$this->context->valueRegistry->record([])
				);
			}
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid parameter value");
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}