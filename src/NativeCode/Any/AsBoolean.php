<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\BooleanType;
use Walnut\Lang\Blueprint\Type\FalseType;
use Walnut\Lang\Blueprint\Type\TrueType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\NativeCode\CastAsBoolean;

final readonly class AsBoolean implements NativeMethod {

	private CastAsBoolean $castAsBoolean;

	public function __construct() {
		$this->castAsBoolean = new CastAsBoolean();
	}

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): BooleanType|TrueType|FalseType {
		return $this->castAsBoolean->analyseType(
			$programRegistry->typeRegistry->boolean,
			$programRegistry->typeRegistry->true,
			$programRegistry->typeRegistry->false,
			$targetType
		);
	}

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		$targetValue = $target;
		
        return ($programRegistry->valueRegistry->boolean(
            $this->castAsBoolean->evaluate($targetValue)
        ));
	}

}