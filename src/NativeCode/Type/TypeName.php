<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\MetaType;
use Walnut\Lang\Blueprint\Type\NamedType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class TypeName implements NativeMethod {

	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		TypeInterface $targetType,
		TypeInterface $parameterType,
	): TypeInterface {
		if ($targetType instanceof TypeType) {
			$refType = $targetType->refType;
			if ($refType instanceof MetaType) {
				if (in_array($refType->value, [
					MetaTypeValue::Named,
					MetaTypeValue::Atom,
					MetaTypeValue::Enumeration,
					MetaTypeValue::Alias,
					MetaTypeValue::Data,
					MetaTypeValue::Open,
					MetaTypeValue::Sealed,
				], true)) {
					return $typeRegistry->string(1);
				}
			}
			if ($refType instanceof NamedType) {
				return $typeRegistry->stringSubset([
					$refType->name->identifier
				]);
			}
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

		if ($targetValue instanceof TypeValue) {
			$typeValue = $targetValue->typeValue;
			if ($typeValue instanceof NamedType) {
				return ($programRegistry->valueRegistry->string($typeValue->name->identifier));
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException(
			sprintf("Invalid target value: %s", $targetValue)
		);
		// @codeCoverageIgnoreEnd
	}

}