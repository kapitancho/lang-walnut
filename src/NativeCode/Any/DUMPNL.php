<?php /** @noinspection SpellCheckingInspection */

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class DUMPNL implements NativeMethod {

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		return $targetType;
	}

	public function execute(
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;

        echo $targetValue, '<br/>', PHP_EOL;
		return $target;
	}

}