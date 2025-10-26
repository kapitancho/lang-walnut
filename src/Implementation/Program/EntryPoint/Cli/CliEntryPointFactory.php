<?php

namespace Walnut\Lang\Implementation\Program\EntryPoint\Cli;

use Walnut\Lang\Blueprint\Compilation\Module\PackageConfigurationProvider;
use Walnut\Lang\Blueprint\Program\EntryPoint\Cli\CliEntryPoint as CliEntryPointInterface;
use Walnut\Lang\Blueprint\Program\EntryPoint\Cli\CliEntryPointBuilder as CliEntryPointBuilderInterface;
use Walnut\Lang\Implementation\Compilation\CompilerFactory;

final class CliEntryPointFactory {

	public function __construct(
		private readonly PackageConfigurationProvider $packageConfiguration
	) {}

	public CliEntryPointBuilderInterface $entryPointBuilder {
		get => new CliEntryPointBuilder(
			new CompilerFactory()->defaultCompiler($this->packageConfiguration)
		);
	}

	public CliEntryPointInterface $entryPoint {
		get => new CliEntryPoint($this->entryPointBuilder);
	}
}