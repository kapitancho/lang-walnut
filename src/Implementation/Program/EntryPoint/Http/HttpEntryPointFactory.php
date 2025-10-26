<?php

namespace Walnut\Lang\Implementation\Program\EntryPoint\Http;

use Walnut\Lang\Blueprint\Compilation\Module\PackageConfigurationProvider;
use Walnut\Lang\Blueprint\Program\EntryPoint\Http\HttpEntryPoint as HttpEntryPointInterface;
use Walnut\Lang\Blueprint\Program\EntryPoint\Http\HttpEntryPointBuilder as HttpEntryPointBuilderInterface;
use Walnut\Lang\Implementation\Compilation\CompilerFactory;

final class HttpEntryPointFactory {

	public function __construct(
		private readonly PackageConfigurationProvider $packageConfiguration
	) {}

	public HttpEntryPointBuilderInterface $entryPointBuilder {
		get => new HttpEntryPointBuilder(
			new CompilerFactory()->defaultCompiler($this->packageConfiguration)
		);
	}

	public HttpEntryPointInterface $entryPoint {
		get => new HttpEntryPoint($this->entryPointBuilder);
	}
}