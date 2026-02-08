<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\DependencyContainer;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AtomType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\NamedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\AtomValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContainerErrorType as DE;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NamedNativeMethod;

final readonly class ValueOf extends NamedNativeMethod {

	protected function isNamedTypeValid(NamedType $namedType, mixed $origin): bool {
		return $namedType->name->equals(new TypeName('DependencyContainer'));
	}

	protected function getValidator(): callable {
		return fn(AtomType $targetType, TypeType $parameterType): ResultType =>
			$this->typeRegistry->result(
				$parameterType->refType,
				$this->typeRegistry->core->dependencyContainerError
			);
	}

	protected function getExecutor(): callable {
		return function(AtomValue $target, TypeValue $parameter): Value {
			$type = $parameter->typeValue;
			$result = $this->dependencyContainer->valueForType($type);
			if ($result instanceof Value) {
				return $result;
			}
			return $this->valueRegistry->error(
				$this->valueRegistry->core->dependencyContainerError(
					$this->valueRegistry->record([
						'targetType' => $this->valueRegistry->type($type),
						'errorOnType' => $this->valueRegistry->type($result->type),
						'errorMessage' => $this->valueRegistry->string(
							match($result->errorType) {
								DE::circularDependency => 'Circular dependency',
								DE::ambiguous => 'Ambiguous dependency',
								DE::notFound => 'Dependency not found',
								DE::unsupportedType => 'Unsupported type',
								DE::errorWhileCreatingValue => 'Error returned while creating value',
							} . (is_string($result->message) ? ": " . $result->message : "")
						)
					])
				)
			);
		};
	}

}