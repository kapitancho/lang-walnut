<?php

namespace Walnut\Lang\Blueprint\Function;

use Stringable;
use Walnut\Lang\Blueprint\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Type\Type;

interface CustomMethod extends Method, Stringable {
	public function targetType(): Type;
	public function methodName(): MethodNameIdentifier;
	public function parameterType(): Type;
	public function dependencyType(): Type|null;
	public function returnType(): Type;
	public function functionBody(): FunctionBody;
}