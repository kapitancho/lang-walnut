<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\File;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SealedValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase\FileMethod;

final readonly class Content extends FileMethod {

	protected function getValidator(): callable {
		return fn(SealedType $targetType, NullType $parameterType) =>
			$this->typeRegistry->result(
				$this->typeRegistry->string(),
				$this->typeRegistry->typeByName(
					new TypeName('CannotReadFile')
				)
			);
	}

	protected function getExecutor(): callable {
		return function(SealedValue $target, NullValue $parameter) {
			$path = $target->value->valueOf('path')->literalValue;
			if (!file_exists($path) || !is_readable($path) || ($contents = file_get_contents($path)) === false) {
				return $this->valueRegistry->error(
					$this->valueRegistry->sealed(
						new TypeName('CannotReadFile'),
						$this->valueRegistry->record([
							'file' => $target
						])
					)
				);
			}
			return $this->valueRegistry->string($contents);
		};
	}
}