<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Error;

use Walnut\Lang\Almond\Engine\Blueprint\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Type\EnumerationType;

final class UnknownEnumerationValue extends EngineException {
	public function __construct(
		public readonly EnumerationType      $enumerationType,
		public readonly EnumerationValueName $valueName,
	) {
		parent::__construct(
			sprintf(
				'The enumeration type "%s" does not contain a value with the name "%s", ' .
				$enumerationType->name,
				$valueName
			)
		);
	}

	public static function of(
		EnumerationType      $enumerationType,
		EnumerationValueName $valueName,
	): never {
		throw new self($enumerationType, $valueName);
	}
}