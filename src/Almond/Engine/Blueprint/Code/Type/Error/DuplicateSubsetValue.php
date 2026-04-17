<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Common\EngineException;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\EnumerationValueName;

final class DuplicateSubsetValue extends EngineException {
	public function __construct(
		public readonly string|Number $valueName,
	) {
		parent::__construct(
			sprintf(
				'The value "%s" is already in the subset type',
				$valueName
			)
		);
	}

	/** @throws self */
	public static function of(
		string|Number $valueName,
	): never {
		throw new self($valueName);
	}
}