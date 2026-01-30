<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Type\FalseType as FalseTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\TrueType as TrueTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Value\BooleanValue as BooleanValueInterface;

interface BooleanType extends EnumerationType {
	public TrueTypeInterface $trueType { get; }
	public BooleanValueInterface $trueValue { get; }
	public FalseTypeInterface $falseType { get; }
	public BooleanValueInterface $falseValue { get; }
}