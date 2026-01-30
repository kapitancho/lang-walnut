<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder;

use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramContext;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\CodeMapper;

interface ProgramBuilderFactory {
	public function forContext(ProgramContext $programContext, CodeMapper $codeMapper): ProgramBuilder;
}