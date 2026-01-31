<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AtomType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType as NullTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\AtomValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue as NullValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Implementation\Code\Value\BuiltIn\NullValue;

final readonly class NullType implements NullTypeInterface, JsonSerializable, AtomType {

	public AtomValue&NullValueInterface $value;

	public function __construct(
		public TypeName $name,
	) {
		$this->value = new NullValue($this);
	}

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure {
		if ($request->value instanceof NullValueInterface) {
			return $request->ok($request->value);
		}
		return $request->withError(
			"The value should be 'null'",
			$this
		);
	}

	public function isSubtypeOf(Type $ofType): bool {
		return match(true) {
			$ofType instanceof NullTypeInterface => true,
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
			default => false
		};
	}

	public function __toString(): string {
		return 'Null';
	}

	public function validate(ValidationRequest $request): ValidationResult {
		return $request->ok();
	}

	public function jsonSerialize(): array {
		return ['type' => 'Null'];
	}

}