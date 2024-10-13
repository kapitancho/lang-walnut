<?php

namespace Walnut\Lang\Blueprint\Code\NativeCode;

use Walnut\Lang\Blueprint\Type\Type;

interface NativeCodeTypeMapper {
	/** @return array<string> */
	public function getTypesFor(Type $type): array;
}