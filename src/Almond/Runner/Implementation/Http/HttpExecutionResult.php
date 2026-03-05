<?php

namespace Walnut\Lang\Almond\Runner\Implementation\Http;

use Walnut\Lang\Almond\AST\Blueprint\Node\RootNode;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Program;
use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramContext;
use Walnut\Lang\Almond\Runner\Blueprint\Http\HttpExecutionResult as HttpExecutionResultInterface;
use Walnut\Lang\Almond\Runner\Blueprint\Http\Message\HttpResponse;
use Walnut\Lang\Almond\Source\Blueprint\LookupContext\ModuleLookupContext;

final readonly class HttpExecutionResult implements HttpExecutionResultInterface {
	public function __construct(
		public HttpResponse $returnValue,
		public Program $program,
		public ProgramContext $programContext,
		public ModuleLookupContext $moduleLookupContext,
		public RootNode $rootNode,
	) {}
}