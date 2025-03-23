<?php

namespace Walnut\Lang\Implementation\Program\EntryPoint\Http;

use Walnut\Lang\Blueprint\Program\EntryPoint\Http\HttpEntryPoint as HttpEntryPointInterface;
use Walnut\Lang\Blueprint\Program\EntryPoint\Http\HttpEntryPointBuilder as HttpEntryPointBuilderInterface;
use Walnut\Lang\Implementation\Compilation\CompilerFactory;

final class HttpEntryPointFactory {

	/** @param array<string, string> $packageRoots */
	public function __construct(
		private readonly string $defaultRoot,
		private readonly array $packageRoots,
	) {}

	public HttpEntryPointBuilderInterface $entryPointBuilder {
		get => new HttpEntryPointBuilder(
			new CompilerFactory()->compiler(
				$this->defaultRoot,
				$this->packageRoots
			)
		);
	}

	public HttpEntryPointInterface $entryPoint {
		get => new HttpEntryPoint($this->entryPointBuilder);
	}
}