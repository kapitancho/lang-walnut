<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Common\Range\LengthRange;
use Walnut\Lang\Blueprint\Type\ArrayType as ArrayTypeInterface;
use Walnut\Lang\Blueprint\Type\ProxyNamedType;
use Walnut\Lang\Blueprint\Type\Type;

final class ArrayType implements ArrayTypeInterface, JsonSerializable {

	private readonly Type $realItemType;

    public function __construct(
		private readonly Type $declaredItemType,
		public readonly LengthRange $range
    ) {}

	public Type $itemType {
		get {
			return $this->realItemType ??= $this->declaredItemType instanceof ProxyNamedType ?
				$this->declaredItemType->actualType : $this->declaredItemType;
		}
	}

    public function isSubtypeOf(Type $ofType): bool {
        return match(true) {
            $ofType instanceof ArrayTypeInterface =>
                $this->itemType->isSubtypeOf($ofType->itemType) &&
                $this->range->isSubRangeOf($ofType->range),
            $ofType instanceof SupertypeChecker =>
                $ofType->isSupertypeOf($this),
            default => false
        };
    }

	public function __toString(): string {
		$itemType = $this->itemType;
		$type = "Array<$itemType, $this->range>";
		return str_replace(["<Any, ..>", "<Any, ", ", ..>"], ["", "<", ">"], $type);
	}

	public function jsonSerialize(): array {
		return [
			'type' => 'Array',
			'itemType' => $this->itemType,
			'range' => $this->range
		];
	}
}