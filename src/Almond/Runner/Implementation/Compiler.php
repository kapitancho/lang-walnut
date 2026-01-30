<?php

namespace Walnut\Lang\Almond\Runner\Implementation;

use Walnut\Lang\Almond\AST\Blueprint\Parser\ModuleDependencyException;
use Walnut\Lang\Almond\AST\Blueprint\Parser\ParserException;
use Walnut\Lang\Almond\AST\Implementation\Builder\NodeBuilderFactory;
use Walnut\Lang\Almond\AST\Implementation\Parser\NodeImporter;
use Walnut\Lang\Almond\AST\Implementation\Parser\TransitionLogger;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Implementation\Program\ProgramContextFactory;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\CodeMapper;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\SourceLocator;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationFailure;
use Walnut\Lang\Almond\ProgramBuilder\Implementation\Builder\ProgramBuilderFactory;
use Walnut\Lang\Almond\ProgramBuilder\Implementation\CodeMapper\NoopCodeMapper;
use Walnut\Lang\Almond\ProgramBuilder\Implementation\Iterator\NodeIteratorFactory;
use Walnut\Lang\Almond\ProgramBuilder\Implementation\ProgramBuilderGateway;
use Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator\CompositePreBuildValidator;
use Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator\PreBuildValidationRequestFactory;
use Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator\PreBuildValidatorProvider;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationFailure;
use Walnut\Lang\Almond\Runner\Implementation\Compilation\CompiledProgram;
use Walnut\Lang\Almond\Runner\Implementation\Compilation\Error\CompilationErrorTransformer;
use Walnut\Lang\Almond\Runner\Implementation\Compilation\Error\CompilationFailureTransformer;
use Walnut\Lang\Almond\Source\Blueprint\SourceFinder\SourceFinder;
use Walnut\Lang\Almond\Source\Implementation\LookupContext\CachedModuleLookupContext;
use Walnut\Lang\Almond\Source\Implementation\LookupContext\PrecompilerModuleLookupContext;
use Walnut\Lang\Almond\Source\Implementation\PackageConfiguration\JsonFilePackageConfigurationProvider;
use Walnut\Lang\Almond\Source\Implementation\Precompiler\EmptyPrecompiler;
use Walnut\Lang\Almond\Source\Implementation\Precompiler\TemplatePrecompiler;
use Walnut\Lang\Almond\Source\Implementation\Precompiler\TestPrecompiler;
use Walnut\Lang\Almond\Source\Implementation\SourceFinder\CompositeSourceFinder;
use Walnut\Lang\Almond\Source\Implementation\SourceFinder\PackageBasedSourceFinder;

final readonly class Compiler {

	private function __construct(
		private string                        $startModule,
		private CodeMapper&SourceLocator      $codeMapper,
		private CompositeSourceFinder         $sourceFinder,
	) {}

	public static function builder(): self {
		return new self(
			'main',
			new NoopCodeMapper(),
			new CompositeSourceFinder()
		);
	}

	public function withStartModule(string $startModule): self {
		return clone($this, ['startModule' => $startModule]);
	}

	public function withCodeMapper(CodeMapper&SourceLocator $codeMapper): self {
		return clone($this, ['codeMapper' => $codeMapper]);
	}

	public function withAddedJsonConfig(string $jsonConfig): self {
		return clone($this, [
			'sourceFinder' =>
				new CompositeSourceFinder(... [
					... $this->sourceFinder->sourceFinders,
					new PackageBasedSourceFinder(
					new JsonFilePackageConfigurationProvider($jsonConfig)
					)
				])
		]);
	}

	public function withAddedSourceFinder(SourceFinder $sourceFinder): self {
		return clone($this, [
			'sourceFinder' =>
				new CompositeSourceFinder(... [
					... $this->sourceFinder->sourceFinders,
					$sourceFinder
				])
		]);
	}

	public function compile(): CompiledProgram|CompilationFailure {
		//Engine:
		$programContext = new ProgramContextFactory()->newProgramContext();

		//Source:
		$lookupContext = new CachedModuleLookupContext(
			new PrecompilerModuleLookupContext(
				$this->sourceFinder,
				[
					new TestPrecompiler(),
					new EmptyPrecompiler(),
					new TemplatePrecompiler(),
				]
			)
		);

		//AST:
		$nodeImporter = new NodeImporter(
			new TransitionLogger(),
			new NodeBuilderFactory(),
		);

		$compilationFailureTransformer = new CompilationFailureTransformer(
			$programContext,
			$lookupContext,
			new CompilationErrorTransformer(
				$this->codeMapper
			)
		);

		try {
			$rootNode = $nodeImporter->importFromSource(
				$this->startModule,
				new ModuleContentProviderAdapter($lookupContext)
			);
		} catch (ModuleDependencyException $exception) {
			return $compilationFailureTransformer->fromModuleDependencyException($exception);
		} catch (ParserException $exception) {
			return $compilationFailureTransformer->fromParserException($exception);
		}

		$nodeIteratorFactory = new NodeIteratorFactory();
		$preBuildValidationRequestFactory = new PreBuildValidationRequestFactory($nodeIteratorFactory);
		$preBuildValidationRequest = $preBuildValidationRequestFactory->newRequest($rootNode);

		$preBuildValidatorProvider = new PreBuildValidatorProvider();
		$compositePreBuildValidator = new CompositePreBuildValidator(
			... $preBuildValidatorProvider->validators
		);

		$fullPreBuildValidationResult = $compositePreBuildValidator->validate($preBuildValidationRequest);

		if ($fullPreBuildValidationResult instanceof PreBuildValidationFailure) {
			return $compilationFailureTransformer
				->fromPreBuildValidationFailure(
					$fullPreBuildValidationResult,
					$rootNode
				);
		}

		//Program Builder:
		$gateway = new ProgramBuilderGateway(new ProgramBuilderFactory());
		$gateway->build(
			$rootNode,
			$programContext,
			$this->codeMapper
		);

		$program = $programContext->validateAndBuildProgram();
		if ($program instanceof ValidationResult) {
			return $compilationFailureTransformer
				->fromPostBuildValidationFailure(
					$program,
					$rootNode
				);
		}
		return new CompiledProgram(
			$program,
			$programContext,
			$lookupContext,
			$rootNode
		);
	}

}