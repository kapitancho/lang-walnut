<?php

namespace Walnut\Lang\Implementation\Function;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\UnknownContextVariable;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Function\FunctionContextFiller;
use Walnut\Lang\Blueprint\Function\UserlandFunction as UserlandFunctionInterface;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyError;
use Walnut\Lang\Blueprint\Program\DependencyContainer\UnresolvableDependency;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class UserlandFunction implements UserlandFunctionInterface {

	public function __construct(
		private FunctionContextFiller      $functionContextFiller,
		public string                      $displayName,
		public Type                        $targetType,
		public Type                        $parameterType,
		public Type                        $returnType,
		public VariableNameIdentifier|null $parameterName,
		public Type                        $dependencyType,
		public FunctionBody                $functionBody
	) {}

	/** @throws AnalyserException */
	private function checkType(string $parameterName, Type $actualType, Type $expectedType): void {
		if (!$actualType->isSubtypeOf($expectedType)) {
			throw new AnalyserException(
				sprintf(
					"Error in %s: expected a %s value of type %s, got %s",
					$this->displayName, $parameterName, $expectedType, $actualType
				)
			);
		}
	}

	/** @throws AnalyserException */
	public function selfAnalyse(
		AnalyserContext $analyserContext
	): void {
		$analyserContext = $this->functionContextFiller->fillAnalyserContext(
			$analyserContext,
			$this->targetType,
			$this->parameterType,
			$this->parameterName,
			$this->dependencyType
		);
		try {
			$returnType = $this->functionBody->analyse($analyserContext);
			if (!$returnType->isSubtypeOf($this->returnType)) {
				throw new AnalyserException(
					sprintf(
						"Error in %s: expected a return value of type %s, got %s",
						$this->displayName, $this->returnType, $returnType
					)
				);
			}
		} catch (UnknownContextVariable $e) {
			throw new AnalyserException(
				sprintf(
					"Unknown variable '%s' in function body",
					$e->variableName
				)
			);
		}
	}

	/** @throws AnalyserException */
	public function analyse(
		Type $targetType,
		Type $parameterType
	): Type {
		$this->checkType('target', $targetType, $this->targetType);
		$this->checkType('parameter', $parameterType, $this->parameterType);
		return $this->returnType;
	}

	/** @throws ExecutionException */
	private function checkValueType(string $parameterName, Value $value, Type $expectedType): void {
		if (!$value->type->isSubtypeOf($expectedType)) {
			// @codeCoverageIgnoreStart
			throw new ExecutionException(
				sprintf(
					"Error in %s: expected a %s value of type < %s >, got %s for value %s",
						$this->displayName, $parameterName, $expectedType,
						$value->type,
						$value
				)
			);
			// @codeCoverageIgnoreEnd
		}
	}

	/** @throws ExecutionException */
	public function execute(
		ExecutionContext            $executionContext,
		Value|null $targetValue,
		Value      $parameterValue,
	): Value {
		if ($targetValue) {
			$this->checkValueType('target', $targetValue, $this->targetType);
		}
		$this->checkValueType('parameter', $parameterValue, $this->parameterType);

		$dependencyValue = $this->dependencyType instanceof NothingType ? null :
			$executionContext->programRegistry->dependencyContainer->valueByType($this->dependencyType);

		if ($dependencyValue instanceof DependencyError) {
			// @codeCoverageIgnoreStart
			$vr = $executionContext->programRegistry->valueRegistry;
			return $vr->error(
				$vr->dataValue(
					new TypeNameIdentifier('DependencyContainerError'),
					$vr->record([
						'targetType' => $vr->type($this->dependencyType),
						'errorOnType' => $vr->type($dependencyValue->type),
						'errorMessage' => $vr->string(
							match($dependencyValue->unresolvableDependency) {
								UnresolvableDependency::circularDependency => 'Circular dependency',
								UnresolvableDependency::ambiguous => 'Ambiguous dependency',
								UnresolvableDependency::notFound => 'Dependency not found',
								UnresolvableDependency::unsupportedType => 'Unsupported type',
								UnresolvableDependency::errorWhileCreatingValue => 'Error returned while creating value',
							}
						),
						'errorType' => $vr->enumerationValue(
							new TypeNameIdentifier('DependencyContainerErrorType'),
							new EnumValueIdentifier(ucfirst($dependencyValue->unresolvableDependency->name))
						),
					])
				)
			);
			// @codeCoverageIgnoreEnd
		}

		$executionContext = $this->functionContextFiller->fillExecutionContext(
			$executionContext,
			$this->targetType,
			$targetValue,
			$this->parameterType,
			$parameterValue,
			$this->parameterName,
			$this->dependencyType,
			$dependencyValue
		);
		try {
			$returnValue = $this->functionBody->execute($executionContext);
		// @codeCoverageIgnoreStart
		} catch (UnknownContextVariable $e) {
			throw new ExecutionException(
				sprintf(
					"Error in %s: unknown variable '%s' in function body",
					$this->displayName, $e->variableName
				)
			);
		}
		// @codeCoverageIgnoreEnd
		$this->checkValueType('return', $returnValue, $this->returnType);
		return $returnValue;
	}

}