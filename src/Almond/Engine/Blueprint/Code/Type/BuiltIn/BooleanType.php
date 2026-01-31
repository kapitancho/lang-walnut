<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FalseType as FalseTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TrueType as TrueTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue as BooleanValueInterface;

interface BooleanType extends EnumerationType {
	public TrueTypeInterface $trueType { get; }
	public BooleanValueInterface $trueValue { get; }
	public FalseTypeInterface $falseType { get; }
	public BooleanValueInterface $falseValue { get; }
}