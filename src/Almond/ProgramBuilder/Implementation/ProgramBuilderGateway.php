<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation;

use Walnut\Lang\Almond\AST\Blueprint\Node\RootNode;
use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramContext;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\ProgramBuilderFactory;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\CodeMapper;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\ProgramBuilderGateway as ProgramBuilderGatewayInterface;

final readonly class ProgramBuilderGateway implements ProgramBuilderGatewayInterface {
	public function __construct(private ProgramBuilderFactory $programBuilderFactory) {}

	public function build(
		RootNode $source,
		ProgramContext $target,
		CodeMapper $codeMapper,
	): void {
		$this->programBuilderFactory
			->forContext($target, $codeMapper)
			->compileProgram($source);
	}
}