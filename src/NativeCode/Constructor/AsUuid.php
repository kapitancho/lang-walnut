<?php

namespace Walnut\Lang\NativeCode\Constructor;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\AtomType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\AtomValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class AsUuid implements NativeMethod {
	use BaseType;

	public function analyse(TypeRegistry $typeRegistry, MethodFinder $methodFinder, Type $targetType, Type $parameterType): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof AtomType && $targetType->name->equals(new TypeNameIdentifier('Constructor'))) {
			if ($parameterType instanceof StringType || $parameterType instanceof StringSubsetType) {
				$mayBeInvalid = true;
				$resultType = $typeRegistry->string();
				if ($parameterType instanceof StringSubsetType) {
					$anyInvalid = false;
					foreach($parameterType->subsetValues as $subsetValue) {
						if (!$this->isValidUuid($subsetValue)) {
							$anyInvalid = true;
							break;
						}
					}
					if (!$anyInvalid) {
						$mayBeInvalid = false;
					}
				}
				if ($mayBeInvalid) {
					$resultType = $typeRegistry->result(
						$resultType,
						$typeRegistry->data(
							new TypeNameIdentifier('InvalidUuid')
						)
					);
				}
				return $resultType;
			}
			// @codeCoverageIgnoreStart
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(ProgramRegistry $programRegistry, Value $target, Value $parameter): Value {
		if ($target instanceof AtomValue && $target->type->name->equals(
			new TypeNameIdentifier('Constructor')
		)) {
			$v = $parameter;
			if ($v instanceof StringValue) {
				if ($this->isValidUuid($v->literalValue)) {
					return $parameter;
				}
				return (
					$programRegistry->valueRegistry->error(
						$programRegistry->valueRegistry->dataValue(
							new TypeNameIdentifier('InvalidUuid'),
							$v
						)
					)
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

	private function isValidUuid(string $uuid): bool {
		return preg_match(
			'/[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-([89ab])[a-f0-9]{3}-[a-f0-9]{12}/',
			$uuid
		);
	}

}