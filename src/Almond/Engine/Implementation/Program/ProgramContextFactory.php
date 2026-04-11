<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Program;

use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramContext as ProgramContextInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramContextFactory as ProgramContextFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResultCollector;

final class ProgramContextFactory implements ProgramContextFactoryInterface {

	/** @param array<string, string> $nativeExtensionNamespaces */
	public function newProgramContext(
		ValidationResultCollector $validationResultCollector,
		array $nativeExtensionNamespaces = []
	): ProgramContextInterface {
		return new ProgramContext(
			$validationResultCollector,
			$nativeExtensionNamespaces
		);
	}
}