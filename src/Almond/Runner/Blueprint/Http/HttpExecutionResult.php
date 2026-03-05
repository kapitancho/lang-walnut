<?php

namespace Walnut\Lang\Almond\Runner\Blueprint\Http;

use Walnut\Lang\Almond\Runner\Blueprint\Compilation\CompiledProgram;
use Walnut\Lang\Almond\Runner\Blueprint\Http\Message\HttpResponse;

interface HttpExecutionResult extends CompiledProgram {
	public HttpResponse $returnValue { get; }
}