<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Common\Range\LengthRange;
use Walnut\Lang\Blueprint\Type\MapType as MapTypeInterface;
use Walnut\Lang\Blueprint\Type\ProxyNamedType;
use Walnut\Lang\Blueprint\Type\SupertypeChecker;
use Walnut\Lang\Blueprint\Type\Type;

final class MapType implements MapTypeInterface, JsonSerializable {

	private readonly Type $realKeyType;
	private readonly Type $realItemType;

    public function __construct(
		private readonly Type $declaredKeyType,
		private readonly Type $declaredItemType,
		public readonly LengthRange $range
    ) {}

	public Type $keyType {
		get {
			return $this->realKeyType ??= $this->declaredKeyType instanceof ProxyNamedType ?
				$this->declaredKeyType->actualType : $this->declaredKeyType;
		}
	}

	public Type $itemType {
		get {
			return $this->realItemType ??= $this->declaredItemType instanceof ProxyNamedType ?
				$this->declaredItemType->actualType : $this->declaredItemType;
		}
	}

    public function isSubtypeOf(Type $ofType): bool {
        return match(true) {
            $ofType instanceof MapTypeInterface =>
                $this->itemType->isSubtypeOf($ofType->itemType) &&
                $this->keyType->isSubtypeOf($ofType->keyType) &&
                $this->range->isSubRangeOf($ofType->range),
            $ofType instanceof SupertypeChecker =>
                $ofType->isSupertypeOf($this),
            default => false
        };
    }

	public function __toString(): string {
		$itemType = $this->itemType;
		$keyTypeStr = (string)$this->keyType;
		$keyType = $keyTypeStr === 'String' ? '' : "$keyTypeStr:";
		$type = "Map<$keyType$itemType, $this->range>";
		return str_replace(["<Any, ..>", "<Any, ", ", ..>"], ["", "<", ">"], $type);
	}

	public function jsonSerialize(): array {
		return [
			'type' => 'Map',
			'keyType' => $this->keyType,
			'itemType' => $this->itemType,
			'range' => $this->range
		];
	}
}