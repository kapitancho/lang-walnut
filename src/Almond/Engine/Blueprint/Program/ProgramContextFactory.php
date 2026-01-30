<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Program;

use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramContext as ProgramContextInterface;

interface ProgramContextFactory {
	public function newProgramContext(array $nativeExtensionNamespaces = []): ProgramContextInterface;
}