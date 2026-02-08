<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\Composite;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class SortHelper {
	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(
		ArrayType|MapType|SetType|MutableType $returnType,
		ArrayType|MapType|SetType $targetType,
		Type $parameterType,
		mixed $origin
	): ValidationSuccess|ValidationFailure {
		$r = $this->typeRegistry;
		$itemType = $targetType->itemType;
		if ($itemType->isSubtypeOf($r->string()) || $itemType->isSubtypeOf(
			$r->union([
				$r->integer(),
				$r->real()
			])
		)) {
			$pType = $r->union([
				$r->null,
				$r->record([
					'reverse' => $r->boolean
				], null)
			]);
			if ($parameterType->isSubtypeOf($pType)) {
				return $this->validationFactory->validationSuccess($returnType);
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf(
					"The parameter type %s is not a subtype of %s",
					$parameterType,
					$pType
				),
				origin: null
			);
		}
		return $this->validationFactory->error(
			ValidationErrorType::invalidTargetType,
			sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
			$origin
		);
	}

	public function execute(
		TupleValue|RecordValue|SetValue $target,
		Value $parameter,
		callable $valueBuilder
	): Value {
		if ($parameter instanceof NullValue || (
			$parameter instanceof RecordValue &&
			($rev = $parameter->values['reverse'] ?? null) instanceof BooleanValue
		)) {
			$reverse = isset($rev) ? $rev->literalValue : false;
			$sort = $reverse ? arsort(...) : asort(...);

			$values = $target->values;

			$rawValues = [];
			$hasStrings = false;
			$hasNumbers = false;
			foreach($values as $key => $value) {
				if ($value instanceof StringValue) {
					$hasStrings = true;
				} elseif ($value instanceof IntegerValue || $value instanceof RealValue) {
					$hasNumbers = true;
				} else {
					// @codeCoverageIgnoreStart
					throw new ExecutionException("Invalid target value");
					// @codeCoverageIgnoreEnd
				}
				$rawValues[$key] = (string)$value->literalValue;
			}
			if ($hasStrings) {
				if ($hasNumbers) {
					// @codeCoverageIgnoreStart
					throw new ExecutionException("Invalid target value");
					// @codeCoverageIgnoreEnd
				}
				$sort($rawValues, SORT_STRING);
				return $valueBuilder(array_map(
					fn(string $value) => $this->valueRegistry->string($value),
					$rawValues
				));
			}
			$sort($rawValues, SORT_NUMERIC);
			return $valueBuilder(array_map(
				fn(string $value) => str_contains((string)$value, '.') ?
					$this->valueRegistry->real((float)$value) :
					$this->valueRegistry->integer((int)$value),
				$rawValues
			));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}