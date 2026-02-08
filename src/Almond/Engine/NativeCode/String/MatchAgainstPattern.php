<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\String;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class MatchAgainstPattern implements NativeMethod {
	use BaseType;

	private const string ROUTE_PATTERN_MATCH = '#\{([\w\_]+)\}#';
	private const string ROUTE_PATTERN_REPLACE = '#\{[\w\_]+\}#';
	private const string REPLACE_PATTERN = '(.+?)';

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(Type $targetType, Type $parameterType, mixed $origin): ValidationSuccess|ValidationFailure {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof StringType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof StringType) {
				$maxLength = PlusInfinity::value;
				$keyType = $this->typeRegistry->string();
				if ($parameterType instanceof StringSubsetType) {
					$allPathArgs = [];
					foreach($parameterType->subsetValues as $subsetValue) {
						if (preg_match_all(self::ROUTE_PATTERN_MATCH, $subsetValue, $matches)) {
							$pathArgs = $matches[1];
						} else {
							$pathArgs = [];
						}
						$allPathArgs = array_merge($allPathArgs, $pathArgs);
					}
					$allPathArgs = array_unique($allPathArgs);
					$keyType = count($allPathArgs) ? $this->typeRegistry->stringSubset($allPathArgs) : $this->typeRegistry->nothing;
				}

				return $this->validationFactory->validationSuccess(
					$this->typeRegistry->union([
						$this->typeRegistry->map(
							$this->typeRegistry->string(),
							0,
							$maxLength,
							$keyType
						),
						$this->typeRegistry->false
					])
				);
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
				$origin
			);
		}
		// @codeCoverageIgnoreStart
		return $this->validationFactory->error(
			ValidationErrorType::invalidTargetType,
			sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
			$origin
		);
		// @codeCoverageIgnoreEnd
	}

	public function execute(Value $target, Value $parameter): Value {
		if ($target instanceof StringValue) {
			if ($parameter instanceof StringValue) {
				$path = $parameter->literalValue;

				if (preg_match_all(self::ROUTE_PATTERN_MATCH, $path, $matches)) {
					$pathArgs = $matches[1];
					$path = '^' . preg_replace(self::ROUTE_PATTERN_REPLACE, self::REPLACE_PATTERN, $path) . '$';
				} else {
					$pathArgs = null;
					$path = '^' . $path . '$';
				}
				$path = strtolower($path);
				if (!preg_match('#' . $path . '#', $target->literalValue, $matches)) {
					return $this->valueRegistry->false;
				}
				return is_array($pathArgs) ?
					$this->valueRegistry->record(
						array_map(fn($value) =>
							$this->valueRegistry->string($value),
							array_combine(
								$pathArgs,
								array_slice($matches, 1)
							)
						)
					) :
					$this->valueRegistry->record([]);
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
