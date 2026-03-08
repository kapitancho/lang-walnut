<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Program;

use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramContext as ProgramContextInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramContextFactory as ProgramContextFactoryInterface;

final class ProgramContextFactory implements ProgramContextFactoryInterface {

	/** @param array<string, string> $nativeExtensionNamespaces */
	public function newProgramContext(array $nativeExtensionNamespaces = []): ProgramContextInterface {
		return new ProgramContext($nativeExtensionNamespaces);
	}
}