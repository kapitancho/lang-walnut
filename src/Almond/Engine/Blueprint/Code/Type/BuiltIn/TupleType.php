<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType as ArrayTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\UnknownProperty;

interface TupleType extends ArrayType {
	/** @var list<Type> $types */
	public array $types { get; }
	public Type $restType { get; }

	public ArrayTypeInterface $arrayType { get; }

	public function typeOf(int $index): Type|UnknownProperty;
}