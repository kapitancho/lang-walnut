<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Code\Execution\FunctionReturn;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\NativeCode\CastAsJsonValue;

final readonly class AsJsonValue implements NativeMethod {

	private CastAsJsonValue $castAsJsonValue;

	public function __construct() {
		$this->castAsJsonValue = new CastAsJsonValue();
	}

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType
	): Type {
		$resultType = $typeRegistry->alias(new TypeNameIdentifier('JsonValue'));
		return $this->castAsJsonValue->isSafeToCastType(
			$typeRegistry,
			$methodAnalyser,
			$targetType
		) ? $resultType : $typeRegistry->result(
			$resultType,
			$typeRegistry->withName(new TypeNameIdentifier('InvalidJsonValue'))
		);
	}

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		try {
			$result = $this->castAsJsonValue->getJsonValue($programRegistry, $target);
		} catch (FunctionReturn $return) {
			return $return->typedValue;
		}
		return $result;
	}

}