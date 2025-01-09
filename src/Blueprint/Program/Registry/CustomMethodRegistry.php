<?php

namespace Walnut\Lang\Blueprint\Program\Registry;

use Walnut\Lang\Blueprint\Function\CustomMethod as CustomMethodInterface;

interface CustomMethodRegistry {
	/** @var array<string, list<CustomMethodInterface>> $customMethods */
	public array $customMethods { get; }
}