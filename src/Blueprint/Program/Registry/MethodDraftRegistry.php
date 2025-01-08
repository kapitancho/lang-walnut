<?php

namespace Walnut\Lang\Blueprint\Program\Registry;

use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Function\MethodDraft;
use Walnut\Lang\Blueprint\Function\UnknownMethod;
use Walnut\Lang\Blueprint\Type\Type;

interface MethodDraftRegistry {
	public function methodDraft(Type $targetType, MethodNameIdentifier $methodName): MethodDraft|UnknownMethod;
}