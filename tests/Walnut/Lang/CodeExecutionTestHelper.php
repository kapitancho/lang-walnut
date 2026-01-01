<?php

namespace Walnut\Lang\Test;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Compilation\AST\AstProgramCompilationException;
use Walnut\Lang\Blueprint\Compilation\AST\AstProgramCompiler;
use Walnut\Lang\Blueprint\Compilation\Module\ModuleLookupContext;
use Walnut\Lang\Blueprint\Program\EntryPoint\Http\Message\HttpRequest;
use Walnut\Lang\Blueprint\Program\EntryPoint\Http\Message\HttpResponse;
use Walnut\Lang\Blueprint\Program\ProgramAnalyserException;
use Walnut\Lang\Blueprint\Program\ProgramContext;
use Walnut\Lang\Implementation\AST\Builder\NodeBuilderFactory;
use Walnut\Lang\Implementation\AST\Parser\Parser;
use Walnut\Lang\Implementation\AST\Parser\ParserStateRunner;
use Walnut\Lang\Implementation\AST\Parser\TransitionLogger;
use Walnut\Lang\Implementation\AST\Parser\WalexLexerAdapter;
use Walnut\Lang\Implementation\Compilation\AST\AstCompilerFactory;
use Walnut\Lang\Implementation\Compilation\AST\NoopAstCodeMapper;
use Walnut\Lang\Implementation\Compilation\Module\ModuleImporter;
use Walnut\Lang\Implementation\Program\EntryPoint\Cli\SourceCliEntryPoint;
use Walnut\Lang\Implementation\Program\EntryPoint\EntryPointProvider;
use Walnut\Lang\Implementation\Program\EntryPoint\Http\SourceHttpEntryPoint;
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
			new Parser(new ParserStateRunner(new TransitionLogger(), new NodeBuilderFactory())),
		);
		$this->programContext = new ProgramContextFactory()->programContext;
		$this->programCompiler = new AstCompilerFactory($this->programContext, new NoopAstCodeMapper())->programCompiler;
	}

	protected function executeCodeSnippetAsHttp(string $code, string $declarations, HttpRequest $httpRequest): HttpResponse {
		$this->moduleLookupContext->method('sourceOf')
			->willReturnCallback(fn(string $module) => match($module) {
				'core/core' => file_get_contents(self::PATH . '/core.nut'),
				'$http/message' => file_get_contents(self::PATH . '/http/message.nut'),
				'$http/request-handler' => file_get_contents(self::PATH . '/http/request-handler.nut'),
				'test' => "module test %% \$http/request-handler: $declarations ==> HttpRequestHandler :: ^request: {HttpRequest} => {HttpResponse} %% [~HttpResponseBuilder] :: { $code };",
				default => ''
			});
		$programNode = $this->moduleImporter->importModules('test');
		$this->programCompiler->compileProgram($programNode);
		$program = $this->programContext->analyseAndBuildProgram();

		return new SourceHttpEntryPoint(
			new EntryPointProvider(
				$this->programContext->typeRegistry,
				$this->programContext->valueRegistry,
				$program,
			)
		)->call($httpRequest);
	}

	protected function executeCodeSnippet(string $code, string $typeDeclarations = '', string $valueDeclarations = '', array $parameters = []): string {
		$this->moduleLookupContext->method('sourceOf')
			->willReturnCallback(fn(string $module) => match($module) {
				'core/core' => file_get_contents(self::PATH . '/core.nut'),
				'test' => "module test: $typeDeclarations ::> { $valueDeclarations myFn = ^Array<String> => Any :: { $code }; myFn(#)->printed; };",
				//'test' => "module test: $typeDeclarations $valueDeclarations ::> { myFn = ^Array<String> => Any :: { $code }; myFn(#)->printed; };",
				default => ''
			});
		$programNode = $this->moduleImporter->importModules('test');
		$this->programCompiler->compileProgram($programNode);
		$program = $this->programContext->analyseAndBuildProgram();

		return new SourceCliEntryPoint(
			new EntryPointProvider(
				$this->programContext->typeRegistry,
				$this->programContext->valueRegistry,
				$program,
			)
		)->call(... $parameters);
		/*
		$tr = $this->programContext->typeRegistry;
		$vr = $this->programContext->valueRegistry;
		$ep = $program->getEntryPoint(
			new VariableNameIdentifier('main'),
			$tr->array($tr->string()),
			$tr->string()
		);
		try {
			return $ep->call($vr->tuple(
				array_map(fn(string $arg) => $vr->string($arg), $parameters)
			))->literalValue;
		} catch (Exception $e) {
			//echo json_encode($programNode);
			throw $e;
		}
		*/
	}

	protected function executeErrorCodeSnippet(
		string $analyserMessage,
		string $code,
		string $typeDeclarations = '',
		string $valueDeclarations = '',
		array  $parameters = []
	): void {
		try {
			$this->executeCodeSnippet($code, $typeDeclarations, $valueDeclarations, $parameters);
			self::fail('Expected exception not thrown');
		} catch(ProgramAnalyserException|AstProgramCompilationException $e) {
			self::assertStringContainsString($analyserMessage, $e->getMessage());
		}
	}
}