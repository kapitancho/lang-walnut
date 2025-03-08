<?php

namespace Walnut\Lang\Test\Feature;

use Exception;
use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Compilation\AST\AstProgramCompiler;
use Walnut\Lang\Blueprint\Compilation\Module\ModuleLookupContext;
use Walnut\Lang\Blueprint\Program\ProgramContext;
use Walnut\Lang\Implementation\AST\Builder\ModuleNodeBuilderFactory;
use Walnut\Lang\Implementation\AST\Builder\NodeBuilderFactory;
use Walnut\Lang\Implementation\AST\Parser\Parser;
use Walnut\Lang\Implementation\AST\Parser\TransitionLogger;
use Walnut\Lang\Implementation\AST\Parser\WalexLexerAdapter;
use Walnut\Lang\Implementation\Compilation\AST\AstCompilerFactory;
use Walnut\Lang\Implementation\Compilation\Module\ModuleImporter;
use Walnut\Lang\Implementation\Program\Program;
use Walnut\Lang\Implementation\Program\ProgramContextFactory;

class ProgramContextTestHelper extends TestCase {
	private const string PATH = __DIR__ . '/../../../../core-nut-lib';

	protected ModuleImporter $moduleImporter;
	protected ModuleLookupContext $moduleLookupContext;
	protected ProgramContext $programContext;
	protected AstProgramCompiler $programCompiler;
	protected Program $program;

	protected function getTestCode(): string {
		return '';
	}

	public function setUp(): void {
		parent::setUp();

		$this->moduleLookupContext = $this->createMock(ModuleLookupContext::class);
		$this->moduleLookupContext->method('sourceOf')
			->willReturnCallback(fn(string $module) => match($module) {
				'core/core' => file_get_contents(self::PATH . '/core.nut'),
				'test' => "module test: " . $this->getTestCode(),
				default => ''
			});
		$this->moduleImporter = new ModuleImporter(
			new WalexLexerAdapter(),
			$this->moduleLookupContext,
			new Parser(new TransitionLogger()),
			new NodeBuilderFactory(),
			new ModuleNodeBuilderFactory(),
		);
		$this->programContext = new ProgramContextFactory()->programContext;
		$this->programCompiler = new AstCompilerFactory($this->programContext)->programCompiler;

		$programNode = $this->moduleImporter->importModules('test');
		$this->programCompiler->compileProgram($programNode);
		$this->program = $this->programContext->analyseAndBuildProgram();
	}

}