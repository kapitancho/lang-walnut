<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Type\P;

use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Type\AnyType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Type\TypeType as TypeTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\UnknownType;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Blueprint\Value\StringValue;

final readonly class TypeType implements TypeTypeInterface {
	public function __construct(
		private TypeRegistry $typeRegistry,

		public Type          $refType
	) {}

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure {
		$value = $request->value;
		$tr = $this->typeRegistry;
		if ($value instanceof StringValue) {
			$typeName = $value->literalValue;
			$type = match ($typeName) {
				'Any' => $tr->any,
				'Nothing' => $tr->nothing,
				'Array' => $tr->array(),
				'Map' => $tr->map(),
				'Mutable' => $tr->mutable($tr->any),
				'Type' => $tr->type($tr->any),
				'Null' => $tr->null,
				'True' => $tr->true,
				'False' => $tr->false,
				'Boolean' => $tr->boolean,
				'Integer' => $tr->integerFull(),
				'Real' => $tr->realFull(),
				'String' => $tr->string(),
				default => $tr->typeByName(new TypeName($typeName)),
			};
			if ($type === UnknownType::value) {
				return $request->withError(
					"The string value should be a name of a valid type",
					$this
				);
			}
			//$type = $tr->withName(new TypeNameIdentifier());
			if ($type->isSubtypeOf($this->refType)) {
				return $request->ok($request->valueRegistry->type($type));
			}
			// Should not be reachable
			// @codeCoverageIgnoreStart
			return $request->withError(
				sprintf("The type should be a subtype of %s", $this->refType),
				$this
			);
			// @codeCoverageIgnoreEnd
		}
		return $request->withError(
			"The value should be a string, containing a name of a valid type",
			$this
		);
	}

	public function isSubtypeOf(Type $ofType): bool {
		if ($ofType instanceof TypeTypeInterface) {
			$ofTypeRef = $ofType->refType;
			if ($this->refType instanceof MutableType && $ofTypeRef instanceof MutableType) {
				return $this->refType->valueType->isSubtypeOf($ofTypeRef->valueType);
			}
		}
		return match(true) {
			$ofType instanceof TypeTypeInterface => $this->refType->isSubtypeOf($ofType->refType),
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
			default => false,
		};
	}

	public function __toString(): string {
		if ($this->refType instanceof AnyType) {
			return "Type";
		}
		return sprintf(
			"Type<%s>",
			$this->refType
		);
	}

	public function validate(ValidationRequest $request): ValidationResult {
		return $this->refType->validate($request);
	}

	public function jsonSerialize(): array {
		return ['type' => 'Type', 'refType' => $this->refType];
	}
}