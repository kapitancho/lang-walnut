<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\AtomValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\EmptyValue;

interface EmptyType extends OptionalType, AtomType {
	public NothingType $valueType { get; }
	public EmptyValue&AtomValue $value { get; }
}