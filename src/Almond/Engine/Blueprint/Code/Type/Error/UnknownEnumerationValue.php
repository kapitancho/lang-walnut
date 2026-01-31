<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EnumerationType;
use Walnut\Lang\Almond\Engine\Blueprint\Common\EngineException;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\EnumerationValueName;

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