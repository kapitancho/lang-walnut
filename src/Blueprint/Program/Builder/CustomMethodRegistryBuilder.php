<?php

namespace Walnut\Lang\Blueprint\Program\Builder;

use Walnut\Lang\Blueprint\Program\Registry\CustomMethodDraftRegistry;
use Walnut\Lang\Blueprint\Program\Registry\CustomMethodRegistry;
use Walnut\Lang\Blueprint\Program\Registry\MethodRegistry;

interface CustomMethodRegistryBuilder {
	public function buildFromDrafts(CustomMethodDraftRegistry $customMethodDraftRegistry): MethodRegistry&CustomMethodRegistry;
}