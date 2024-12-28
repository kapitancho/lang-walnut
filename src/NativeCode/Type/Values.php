<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Type\EnumerationSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\MetaType;
use Walnut\Lang\Blueprint\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Type\NullType;
use Walnut\Lang\Blueprint\Type\RealSubsetType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\NullValue;
use Walnut\Lang\Blueprint\Value\TypeValue;

final readonly class Values implements NativeMethod {

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		TypeInterface $targetType,
		TypeInterface $parameterType,
	): TypeInterface {
		if ($parameterType instanceof NullType) {
			if ($targetType instanceof TypeType) {
				$refType = $targetType->refType();
				if ($refType instanceof MetaType) {
					$t = match($refType->value()) {
						MetaTypeValue::Enumeration, MetaTypeValue::EnumerationSubset
							=> $this->context->typeRegistry()->any(),
						MetaTypeValue::IntegerSubset => $this->context->typeRegistry()->integer(),
						MetaTypeValue::RealSubset => $this->context->typeRegistry()->real(),
						MetaTypeValue::StringSubset => $this->context->typeRegistry()->string(),
						default => null
					};
					if ($t) {
						return $this->context->typeRegistry()->array($t, 1);
					}
				}
				if ($refType instanceof IntegerSubsetType ||
					$refType instanceof RealSubsetType ||
					$refType instanceof StringSubsetType ||
					$refType instanceof EnumerationSubsetType
				) {
					$t = match(true) {
						$refType instanceof IntegerSubsetType => $this->context->typeRegistry()->integer(),
						$refType instanceof RealSubsetType => $this->context->typeRegistry()->real(),
						$refType instanceof StringSubsetType => $this->context->typeRegistry()->string(),
						$refType instanceof EnumerationSubsetType => $this->context->typeRegistry()->enumeration(
							$refType->enumeration()->name(),
						),
					};
					$l = count($refType->subsetValues());
					return $this->context->typeRegistry()->array($t, $l, $l);
				}
			}
			// @codeCoverageIgnoreStart
			throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;
		$parameterValue = $parameter->value;
		
		if ($parameterValue instanceof NullValue) {
			if ($targetValue instanceof TypeValue) {
				$refType = $targetValue->typeValue();
				if ($refType instanceof IntegerSubsetType ||
					$refType instanceof RealSubsetType ||
					$refType instanceof StringSubsetType ||
					$refType instanceof EnumerationSubsetType
				) {
					return TypedValue::forValue($this->context->valueRegistry()->tuple(
						array_values(
							array_unique(
								$refType->subsetValues()
							)
						)
					));
				}
			}
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid target value");
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}