<?php

namespace Walnut\Lang\Implementation\Program\EntryPoint;

use Walnut\Lang\Blueprint\Program\EntryPoint\CliEntryPoint as CliEntryPointInterface;
use Walnut\Lang\Blueprint\Program\EntryPoint\CliEntryPointBuilder as CliEntryPointBuilderInterface;
use Walnut\Lang\Implementation\Compilation\CompilerFactory;

final class CliEntryPointFactory {

	/** @param array<string, string> $packageRoots */
	public function __construct(
		private readonly string $defaultRoot,
		private readonly array $packageRoots,
	) {}

	public CliEntryPointBuilderInterface $entryPointBuilder {
		get => new CliEntryPointBuilder(
			new CompilerFactory()->compiler(
				$this->defaultRoot,
				$this->packageRoots
			)
		);
	}

	public CliEntryPointInterface $entryPoint {
		get => new CliEntryPoint($this->entryPointBuilder);
	}
}