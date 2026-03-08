<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Random;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AtomType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\AtomValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\OpenValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<AtomType, NullType, AtomValue, NullValue> */
final readonly class Uuid extends NativeMethod {

	protected function getValidator(): callable {
		return fn(AtomType $targetType, NullType $parameterType): OpenType =>
			$this->typeRegistry->core->uuid;
	}

	protected function getExecutor(): callable {
		return function(AtomValue $target, NullValue $parameter): OpenValue {
			/** @var list<int> $arr */
			$arr = array_values((array)unpack('N1a/n4b/N1c', random_bytes(16)));
			$source = (int)(microtime(true) * 0x10000);
			$arr[0] = $source >> 16;
			$arr[1] = $source & 0xffff;
			$arr[2] = ($arr[2] & 0x0fff) | 0x4000;
			$arr[3] = ($arr[3] & 0x3fff) | 0x8000;
			$uuid = vsprintf('%08x-%04x-%04x-%04x-%04x%08x', $arr);
			return $this->valueRegistry->core->uuid(
				$this->valueRegistry->string($uuid)
			);
		};
	}

}
