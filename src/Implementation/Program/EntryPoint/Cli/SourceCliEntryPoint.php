<?php

namespace Walnut\Lang\Implementation\Program\EntryPoint\Cli;

use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\EntryPoint\Cli\SourceCliEntryPoint as SourceCliEntryPointInterface;
use Walnut\Lang\Blueprint\Program\EntryPoint\EntryPointProvider;

final readonly class SourceCliEntryPoint implements SourceCliEntryPointInterface {
	public function __construct(
		private EntryPointProvider $entryPointProvider
	) {}

	public function call(string ... $parameters): string {
		$tr = $this->entryPointProvider->typeRegistry;
		$vr = $this->entryPointProvider->valueRegistry;
		$ep = $this->entryPointProvider->program->getEntryPoint(
			new VariableNameIdentifier('main'),
			$tr->array($tr->string()),
			$tr->string()
		);
		$returnValue = $ep->call($vr->tuple(
			array_map(fn(string $arg) => $vr->string($arg), $parameters)
		));
		return $returnValue->literalValue;
	}
}