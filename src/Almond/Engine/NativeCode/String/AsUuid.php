<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\String;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\CoreType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\OpenValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<StringType, NullType, StringValue, NullValue> */
final readonly class AsUuid extends NativeMethod {

	private function isValidUuid(string $uuid): bool {
		return (bool)preg_match(
			'/[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-([89ab])[a-f0-9]{3}-[a-f0-9]{12}/',
			$uuid
		);
	}

	protected function getValidator(): callable {
		return function(StringType $targetType, NullType $parameterType): ResultType|OpenType {
			$mayBeInvalid = true;
			$resultType = $this->typeRegistry->core->uuid;
			if ($targetType instanceof StringSubsetType) {
				$anyInvalid = false;
				foreach($targetType->subsetValues as $subsetValue) {
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
				$resultType = $this->typeRegistry->result(
					$resultType,
					$this->typeRegistry->core->invalidUuid
				);
			}
			return $resultType;
		};
	}

	protected function getExecutor(): callable {
		return fn(StringValue $target, NullValue $parameter): OpenValue|ErrorValue =>
			$this->isValidUuid($target->literalValue) ?
				$this->valueRegistry->open(
					CoreType::Uuid->typeName(),
					$target
				) :
				$this->valueRegistry->error(
					$this->valueRegistry->core->invalidUuid(
						$target
					)
				);
	}

}
