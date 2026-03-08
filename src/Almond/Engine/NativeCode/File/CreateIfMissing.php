<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\File;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BytesType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BytesValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SealedValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase\FileMethod;

final readonly class CreateIfMissing extends FileMethod {

	protected function getValidator(): callable {
		return fn(SealedType $targetType, StringType|BytesType $parameterType) =>
			$this->typeRegistry->result(
				$this->typeRegistry->typeByName(
				new TypeName('File')
				),
				$this->typeRegistry->typeByName(
					new TypeName('CannotWriteFile')
				)
			);
	}

	protected function getExecutor(): callable {
		return function(SealedValue $target, StringValue|BytesValue $parameter) {
			/** @var string $path */
			/** @phpstan-ignore-next-line */
			$path = $target->value->valueOf('path')->literalValue;
			if (!file_exists($path)) {
				if (!is_writable(dirname($path)) || (@file_put_contents($path, $parameter->literalValue)) === false) {
					return $this->valueRegistry->error(
						$this->valueRegistry->sealed(
							new TypeName('CannotWriteFile'),
							$this->valueRegistry->record([
								'file' => $target
							])
						)
					);
				}
			}
			return $target;
		};
	}
}