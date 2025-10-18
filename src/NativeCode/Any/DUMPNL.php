<?php /** @noinspection SpellCheckingInspection */

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class DUMPNL implements NativeMethod {

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		return $targetType;
	}

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		$targetValue = $target;

        echo $targetValue, '<br/>', PHP_EOL;
		return $target;
	}

}