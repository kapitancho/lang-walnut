<?php /** @noinspection PhpUnusedParameterInspection */

namespace Walnut\Lang\Implementation\Code\NativeCode\Hydrator;

use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Blueprint\Program\UnknownType;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\NativeCode\HydrationException;

final readonly class TypeTypeHydrator {
	public function __construct(
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}


	public function hydrateType(Value $value, TypeType $targetType, string $hydrationPath): TypeValue {
		if ($value instanceof StringValue) {
			try {
				$typeName = $value->literalValue;
				$type = match($typeName) {
					'Any' => $this->typeRegistry->any,
					'Nothing' => $this->typeRegistry->nothing,
					'Array' => $this->typeRegistry->array(),
					'Map' => $this->typeRegistry->map(),
					'Mutable' => $this->typeRegistry->mutable($this->typeRegistry->any),
					'Type' => $this->typeRegistry->type($this->typeRegistry->any),
					'Null' => $this->typeRegistry->null,
					'True' => $this->typeRegistry->true,
					'False' => $this->typeRegistry->false,
					'Boolean' => $this->typeRegistry->boolean,
					'Integer' => $this->typeRegistry->integerFull(),
					'Real' => $this->typeRegistry->realFull(),
					'String' => $this->typeRegistry->string(),
					default => $this->typeRegistry->withName(new TypeNameIdentifier($typeName)),
				}				;
				//$type = $this->typeRegistry->withName(new TypeNameIdentifier());
				if ($type->isSubtypeOf($targetType->refType)) {
					return $this->valueRegistry->type($type);
				}
				// Should not be reachable
				// @codeCoverageIgnoreStart
				throw new HydrationException(
					$value,
					$hydrationPath,
					sprintf("The type should be a subtype of %s", $targetType->refType)
				);
				// @codeCoverageIgnoreEnd
			} catch (UnknownType) {
				throw new HydrationException(
					$value,
					$hydrationPath,
					"The string value should be a name of a valid type"
				);
			}
		}
		throw new HydrationException(
			$value,
			$hydrationPath,
			"The value should be a string, containing a name of a valid type"
		);
	}

}