<?php

namespace Walnut\Lang\Blueprint\Program\Registry;

use Walnut\Lang\Blueprint\Function\CustomMethodDraft as CustomMethodDraftInterface;

interface CustomMethodDraftRegistry {
	/** @var array<string, list<CustomMethodDraftInterface>> $customMethods */
	public array $customMethods { get; }
}