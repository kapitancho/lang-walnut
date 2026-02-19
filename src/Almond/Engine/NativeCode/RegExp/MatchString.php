<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\RegExp;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SealedValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<SealedType, StringType, SealedValue, StringValue> */
final readonly class MatchString extends NativeMethod {

	protected function getValidator(): callable {
		return fn(SealedType $targetType, StringType $parameterType): ResultType =>
			$this->typeRegistry->result(
				$this->typeRegistry->core->regExpMatch,
				$this->typeRegistry->core->noRegExpMatch
			);
	}

	protected function getExecutor(): callable {
		return function(SealedValue $target, StringValue $parameter): Value {
			/** @var StringValue $regExpValue */
			$regExpValue = $target->value;
			$result = preg_match(
				$regExpValue->literalValue,
				$parameter->literalValue,
				$matches
			);
			if ($result) {
				return $this->valueRegistry->core->regExpMatch(
					$this->valueRegistry->record([
						'match' => $this->valueRegistry->string($matches[0]),
						'groups' => $this->valueRegistry->tuple(
							array_map(
								fn($match) => $this->valueRegistry->string($match),
								array_slice($matches, 1)
							)
						)
					])
				);
			}
			return $this->valueRegistry->error(
				$this->valueRegistry->core->noRegExpMatch
			);
		};
	}

}
