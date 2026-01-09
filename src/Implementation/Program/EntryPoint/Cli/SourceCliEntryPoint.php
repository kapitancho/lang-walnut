<?php

namespace Walnut\Lang\Implementation\Program\EntryPoint\Cli;

use Walnut\Lang\Blueprint\Program\EntryPoint\Cli\SourceCliEntryPoint as SourceCliEntryPointInterface;
use Walnut\Lang\Blueprint\Program\EntryPoint\EntryPointProvider;
use Walnut\Lang\Blueprint\Type\CoreType;

final readonly class SourceCliEntryPoint implements SourceCliEntryPointInterface {
	public function __construct(
		private EntryPointProvider $entryPointProvider
	) {}

	public function call(string ... $parameters): string {
		$vr = $this->entryPointProvider->valueRegistry;
		$ep = $this->entryPointProvider->program->getEntryPoint(
			CoreType::CliEntryPoint->typeName()
		);
		$returnValue = $ep->call($vr->tuple(
			array_map(fn(string $arg) => $vr->string($arg), $parameters)
		));
		return $returnValue->literalValue;
	}
}