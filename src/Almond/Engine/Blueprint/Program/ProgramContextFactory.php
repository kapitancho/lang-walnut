<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Program;

use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramContext as ProgramContextInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResultCollector;

interface ProgramContextFactory {
	public function newProgramContext(
		ValidationResultCollector $validationResultCollector,
		array $nativeExtensionNamespaces = []
	): ProgramContextInterface;
}