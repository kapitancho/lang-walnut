<?php

namespace Walnut\Lang\Test;

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
use Walnut\Lang\Implementation\Program\ProgramContextFactory;

class CodeExecutionTestHelper extends TestCase {
	private const string PATH = __DIR__ . '/../../../core-nut-lib';

	protected ModuleImporter $moduleImporter;
	protected ModuleLookupContext $moduleLookupContext;
	protected ProgramContext $programContext;
	protected AstProgramCompiler $programCompiler;

	public function setUp(): void {
		parent::setUp();

		$this->moduleLookupContext = $this->createMock(ModuleLookupContext::class);
		$this->moduleImporter = new ModuleImporter(
			new WalexLexerAdapter(),
			$this->moduleLookupContext,
			new Parser(new TransitionLogger()),
			new NodeBuilderFactory(),
			new ModuleNodeBuilderFactory(),
		);
		$this->programContext = new ProgramContextFactory()->programContext;
		$this->programCompiler = new AstCompilerFactory($this->programContext)->programCompiler;

	}

	protected function executeCodeSnippet(string $code, string $declarations = '', array $parameters = []): string {
		$this->moduleLookupContext->method('sourceOf')
			->willReturnCallback(fn(string $module) => match($module) {
				'core' => file_get_contents(self::PATH . '/core.nut'),
				'test' => "module test: $declarations myFn = ^Array<String> => Any :: $code main = ^Array<String> => String :: myFn(#)->printed;",
				default => ''
			});
		$program = $this->moduleImporter->importModules('test');
		$this->programCompiler->compileProgram($program);
		$program = $this->programContext->analyseAndBuildProgram();
		$tr = $this->programContext->typeRegistry;
		$vr = $this->programContext->valueRegistry;
		$ep = $program->getEntryPoint(
			new VariableNameIdentifier('main'),
			$tr->array($tr->string()),
			$tr->string()
		);
		return $ep->call($vr->tuple(
			array_map(fn(string $arg) => $vr->string($arg), $parameters)
		))->literalValue;
	}

	protected function executeErrorCodeSnippet(
		string $analyserMessage,
		string $code,
		string $declarations = '',
		array $parameters = []
	): void {
		try {
			$this->executeCodeSnippet($code, $declarations, $parameters);
			self::fail('Expected exception not thrown');
		} catch(AnalyserException $e) {
			self::assertStringContainsString($analyserMessage, $e->getMessage());
		}
	}
}