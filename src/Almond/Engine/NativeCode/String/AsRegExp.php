<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\String;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\CoreType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SealedValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<StringType, NullType, StringValue, NullValue> */
final readonly class AsRegExp extends NativeMethod {

	private function isValidRegexp(string $pattern): bool {
		return @preg_match($pattern, '') !== false;
	}

	protected function getValidator(): callable {
		return function(StringType $targetType, NullType $parameterType): ResultType|SealedType {
			$mayBeInvalid = true;
			$resultType = $this->typeRegistry->core->regExp;
			if ($targetType instanceof StringSubsetType) {
				$anyInvalid = false;
				foreach($targetType->subsetValues as $subsetValue) {
					if (!$this->isValidRegexp($subsetValue)) {
						$anyInvalid = true;
						break;
					}
				}
				if (!$anyInvalid) {
					$mayBeInvalid = false;
				}
			}
			if ($mayBeInvalid) {
				$resultType = $this->typeRegistry->result(
					$resultType,
					$this->typeRegistry->core->invalidRegExp
				);
			}
			return $resultType;
		};
	}

	protected function getExecutor(): callable {
		return fn(StringValue $target, NullValue $parameter): SealedValue|ErrorValue =>
			$this->isValidRegexp($target->literalValue) ?
				$this->valueRegistry->sealed(
					CoreType::RegExp->typeName(),
					$target
				) :
				$this->valueRegistry->error(
					$this->valueRegistry->core->invalidRegExp(
						$target
					)
				);
	}

}
