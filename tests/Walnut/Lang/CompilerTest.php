<?php

namespace Walnut\Lang\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\AST\Node\RootNode;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Compilation\AST\AstProgramCompilationException;
use Walnut\Lang\Blueprint\Compilation\CompilationResult;
use Walnut\Lang\Blueprint\Compilation\FailedCompilationResult;
use Walnut\Lang\Blueprint\Compilation\Module\ModuleDependencyException;
use Walnut\Lang\Blueprint\Compilation\Module\ModuleLookupContext;
use Walnut\Lang\Blueprint\Compilation\SuccessfulCompilationResult;
use Walnut\Lang\Blueprint\Program\Program;
use Walnut\Lang\Blueprint\Program\ProgramAnalyserException;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\AST\Parser\StringEscapeCharHandler;
use Walnut\Lang\Implementation\Compilation\AST\NodeAstCodeMapper;
use Walnut\Lang\Implementation\Compilation\CompilationErrorBuilder;
use Walnut\Lang\Implementation\Compilation\Compiler;
use Walnut\Lang\Implementation\Compilation\Module\PackageConfiguration\PackageConfiguration;
use Walnut\Lang\Implementation\Compilation\Module\Precompiler\EmptyPrecompiler;
use Walnut\Lang\Implementation\Compilation\Module\Precompiler\TemplatePrecompiler;
use Walnut\Lang\Implementation\Compilation\Module\Precompiler\TestPrecompiler;
use Walnut\Lang\Implementation\Compilation\Module\PrecompilerModuleLookupContext;
use Walnut\Lang\Implementation\Compilation\Module\SourceFinder\PackageBasedSourceFinder;

final class CompilerTest extends TestCase {
	private const string PATH = __DIR__ . '/../../../core-nut-lib';

	private Compiler $compiler;
	private CompilationErrorBuilder $compilationErrorBuilder;

	protected function setUp(): void {
		parent::setUp();

		$codeMapper = new NodeAstCodeMapper();
		$this->compiler = new Compiler(
			new PrecompilerModuleLookupContext(
				new PackageBasedSourceFinder(
					new PackageConfiguration(
						self::PATH,
						['core' => self::PATH]
					)
				),
				[
					'.nut' => new EmptyPrecompiler(),
					'.nut.html' => new TemplatePrecompiler(new StringEscapeCharHandler()),
					'.test.nut' => new TestPrecompiler()
				]
			),
		);
		$this->compilationErrorBuilder = new CompilationErrorBuilder($codeMapper);
	}

	public function testBrokenCompilation(): void {
		$this->expectException(ModuleDependencyException::class);
		$this->compiler->compile('missing');
	}

	public function testBrokenSafeCompilationMissing(): void {
		$result = $this->compiler->safeCompile('missing');
		$this->assertInstanceOf(FailedCompilationResult::class, $result);
		$this->assertInstanceOf(ModuleDependencyException::class, $result->errorState);
		$errors = $this->compilationErrorBuilder->build($result->errorState);
		$this->assertCount(1, $errors);
		$this->assertEquals('Module not found: missing', $errors[0]->errorMessage);
		$this->assertIsArray($errors[0]->location);
	}

	private function getSafeCompiler($mainSource): Compiler {
		$original = new PrecompilerModuleLookupContext(
			new PackageBasedSourceFinder(
				new PackageConfiguration(
					self::PATH,
					['core' => self::PATH]
				)
			),
			[
				'.nut' => new EmptyPrecompiler(),
				'.nut.html' => new TemplatePrecompiler(new StringEscapeCharHandler()),
				'.test.nut' => new TestPrecompiler()
			]
		);

		$l = $this->createMock(ModuleLookupContext::class);
		$l->method('sourceOf')->willReturnCallback(fn(string $source) => match($source) {
			'main' => $mainSource,
			default => $original->sourceOf($source)
		});
		return new Compiler($l);
	}

	public function testBrokenSafeCompilationAst(): void {
		$compiler = $this->getSafeCompiler(<<<NUT
			module main:
			=> { myFn = ^Null => MissingType :: 1; ''; };
		NUT);
		$result = $compiler->safeCompile('main');
		$this->assertInstanceOf(FailedCompilationResult::class, $result);
		$this->assertInstanceOf(RootNode::class, $result->ast);
		$this->assertInstanceOf(AstProgramCompilationException::class, $result->errorState);
		$errors = $this->compilationErrorBuilder->build($result->errorState);
		$this->assertCount(1, $errors);
		$this->assertEquals('main', $errors[0]->moduleName);
		$this->assertEquals("Cannot find type 'MissingType'", $errors[0]->errorMessage);
		$this->assertInstanceOf(SourceLocation::class, $errors[0]->location);
		$this->assertEquals('module main, starting on line 2, column 23, offset 36, ending on line 2, column 36, offset 49', (string)$errors[0]->location);
	}

	public function testBrokenSafeCompilationAnalyse(): void {
		$compiler = $this->getSafeCompiler(<<<NUT
			module main:
			=> { myFn = ^Null => NotANumber :: 1; ''; };
		NUT);
		$result = $compiler->safeCompile('main');
		$this->assertInstanceOf(FailedCompilationResult::class, $result);
		$this->assertInstanceOf(RootNode::class, $result->ast);
		$this->assertInstanceOf(ProgramAnalyserException::class, $result->errorState);
		$errors = $this->compilationErrorBuilder->build($result->errorState);
		$this->assertCount(1, $errors);
		$this->assertEquals('the CLI entry point', $errors[0]->entryDescription);
		$this->assertEquals("Expected a return value of type NotANumber, got Integer[1]", $errors[0]->errorMessage);
	}

	public function testSuccessfulSafeCompilation(): void {
		$compiler = $this->getSafeCompiler(<<<NUT
			module main:
			=> { myFn = ^Null => NotANumber :: NotANumber; ''; };
		NUT);
		$result = $compiler->safeCompile('main');
		$this->assertInstanceOf(SuccessfulCompilationResult::class, $result);
		$this->assertInstanceOf(RootNode::class, $result->ast);
		$this->assertInstanceOf(Program::class, $result->program);
		$this->assertNull( $result->errorState);
	}

	#[DataProvider('sources')]
	public function testCompilation(string $source): void {
		try {
			if (str_ends_with($source, '.test')) {
				$this->assertTrue(true, "Skipping test file: $source");
				return;
			}
			$compilationResult = $this->compiler->compile($source);
			$this->assertInstanceOf(CompilationResult::class, $compilationResult);

			$isExecutable = preg_match('/^(cast\d+|demo-\w+|nwk-\w+|lang-[\w\-]+)$/', $source);
			if ($isExecutable) {
				$program = $compilationResult->program;
				$vr = $compilationResult->programContext->valueRegistry;
				$ep = $program->getEntryPoint(new TypeNameIdentifier('CliEntryPoint'));
				$value = $ep->call($vr->tuple([]));
				$this->assertInstanceOf(Value::class, $value);

				if ($source === 'demo-all') {
					$this->assertNotEquals('{}', json_encode($compilationResult->ast));
				}

				//$this->assertNotEquals('{}', json_encode($compilationResult->programContext));
				//$this->assertNotEquals('', (string)$compilationResult->programContext);
			}
		} catch (ProgramAnalyserException $e) {
			$this->assertStringContainsString(
				'cannot be resolved',
				$e->getMessage(),
				"Compilation of $source failed with: " . $e->getMessage()
			);
		}
	}

	public static function sources(): iterable {
		$sourceRoot = self::PATH;
		foreach(glob("$sourceRoot/*.nut") as $sourceFile) {
			yield [str_replace('.nut', '', basename($sourceFile))];
		}
	}
}