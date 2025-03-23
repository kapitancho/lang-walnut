<?php

namespace Walnut\Lang\Implementation\Program\EntryPoint\Http;

use Walnut\Lang\Blueprint\Compilation\Compiler;
use Walnut\Lang\Blueprint\Program\EntryPoint\Http\HttpEntryPointBuilder as HttpEntryPointBuilderInterface;

final readonly class HttpEntryPointBuilder implements HttpEntryPointBuilderInterface {
	public function __construct(
		private Compiler $compiler
	) {}

	public function build(string $source): SourceHttpEntryPoint {
		return new SourceHttpEntryPoint(
			$this->compiler->compile($source)
		);
	}
}