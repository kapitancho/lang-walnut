<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Range\LengthRange;
use Walnut\Lang\Blueprint\Type\MapType as MapTypeInterface;
use Walnut\Lang\Blueprint\Type\ProxyNamedType;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class MapType implements MapTypeInterface, JsonSerializable {

	private Type $realItemType;

    public function __construct(
		private Type $itemType,
		private LengthRange $range
    ) {}

    public function range(): LengthRange {
        return $this->range;
    }

	public function itemType(): Type {
		return $this->realItemType ??= $this->itemType instanceof ProxyNamedType ?
			$this->itemType->getActualType() : $this->itemType;
	}

    public function isSubtypeOf(Type $ofType): bool {
        return match(true) {
            $ofType instanceof MapTypeInterface =>
                $this->itemType()->isSubtypeOf($ofType->itemType()) &&
                $this->range->isSubRangeOf($ofType->range()),
            $ofType instanceof SupertypeChecker =>
                $ofType->isSupertypeOf($this),
            default => false
        };
    }

	public function __toString(): string {
		$itemType = $this->itemType();
		$type = "Map<$itemType, $this->range>";
		return str_replace(["<Any, ..>", "<Any, ", ", ..>"], ["", "<", ">"], $type);
	}

	public function jsonSerialize(): array {
		return [
			'type' => 'Map',
			'itemType' => $this->itemType,
			'range' => $this->range
		];
	}
}