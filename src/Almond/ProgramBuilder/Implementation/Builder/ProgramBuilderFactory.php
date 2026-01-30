<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Builder;

use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramContext;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\CodeMapper;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\ProgramBuilder;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\ProgramBuilderFactory as ProgramCompilerFactoryInterface;

final readonly class ProgramBuilderFactory implements ProgramCompilerFactoryInterface {

	public function __construct() {}

	public function forContext(ProgramContext $programContext, CodeMapper $codeMapper): ProgramBuilder {
		return new BuilderContext($programContext, $codeMapper)->programBuilder;
	}

}