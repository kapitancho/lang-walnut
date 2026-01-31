<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Common\EngineException;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\EnumerationValueName;

final class DuplicateSubsetValue extends EngineException {
	public function __construct(
		public readonly Type                               $type,
		public readonly EnumerationValueName|string|Number $valueName,
	) {
		parent::__construct(
			sprintf(
				'The type "%s" already contains the value "%s"',
				$type,
				$valueName
			)
		);
	}

	public static function of(
		Type $enumerationType,
		EnumerationValueName|string|Number $valueName,
	): never {
		throw new self($enumerationType, $valueName);
	}
}