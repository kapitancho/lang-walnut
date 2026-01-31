<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AnyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType as TypeTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\UnknownType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

final readonly class TypeType implements TypeTypeInterface, JsonSerializable {
	public function __construct(
		private TypeRegistry $typeRegistry,

		public Type          $refType
	) {}

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure {
		$value = $request->value;
		$tr = $this->typeRegistry;
		if ($value instanceof StringValue) {
			$typeName = $value->literalValue;
			try {
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
			} catch (UnknownType) {
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