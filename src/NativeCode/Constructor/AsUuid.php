<?php

namespace Walnut\Lang\NativeCode\Constructor;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\AtomType;
use Walnut\Lang\Blueprint\Type\CoreType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\AtomValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class AsUuid implements NativeMethod {
	use BaseType;

	public function analyse(TypeRegistry $typeRegistry, MethodAnalyser $methodAnalyser, Type $targetType, Type $parameterType): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof AtomType && $targetType->name->equals(CoreType::Constructor->typeName())) {
			if ($parameterType instanceof StringType) {
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
						$typeRegistry->core->invalidUuid
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
			CoreType::Constructor->typeName()
		)) {
			$v = $parameter;
			if ($v instanceof StringValue) {
				if ($this->isValidUuid($v->literalValue)) {
					return $parameter;
				}
				return $programRegistry->valueRegistry->error(
					$programRegistry->valueRegistry->core->invalidUuid(
						$v
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
		return (bool)preg_match(
			'/[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-([89ab])[a-f0-9]{3}-[a-f0-9]{12}/',
			$uuid
		);
	}

}