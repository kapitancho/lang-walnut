<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Expression\Helper;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\MethodContext;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FalseType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TrueType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableScope;

final readonly class BooleanExpressionHelper {

	public function __construct(
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
		private MethodContext $methodContext,
	) {}

	public function getBooleanType(
		Type $expressionType, mixed $origin
	): ValidationSuccess|ValidationFailure {
		return $this->methodContext->validateCast(
			$expressionType,
			new TypeName('Boolean'),
			$origin
		);
	}

	/** @throws ExecutionException */
	public function getBooleanValue(Value $value): bool {
		return $this->methodContext->executeCast(
			$value,
			new TypeName('Boolean'),
		)->equals(
			$this->valueRegistry->true
		);
	}


	public function scopeVariablesMatch(
		VariableScope $first,
		VariableScope $second
	): bool {
		if ($first === $second) {
			return true;
		}
		$fVars = $first->variables();
		$sVars = $second->variables();
		return
			count($fVars) === count($sVars) &&
			count($fVars) === count(array_intersect($fVars, $sVars));
	}

	public function contextUnion(ValidationContext $first, ValidationContext $second): ValidationContext {
		if ($first->variableScope === $second->variableScope) {
			return $first;
		}
		foreach($second->variableScope->types as $varType) {
			$firstType = $first->variableScope->typeOf($varType->name);
			if ($firstType === $varType->type) {
				continue;
			}
			// @codeCoverageIgnoreStart
			if ($firstType === null) {
				$first = $first->withAddedVariableType($varType->name, $varType->type);
			}
			// @codeCoverageIgnoreEnd
			$first = $first->withAddedVariableType($varType->name,
				$this->typeRegistry->union([
					$firstType,
					$varType->type
				])
			);
		}
		return $first;
	}

	/** @param class-string<TrueType|FalseType> $cutOffType */
	public function validateInContext(
		ValidationContext $validationContext,
		Expression $first,
		Expression $second,
		Expression $origin,
		string $cutOffType,
	): ValidationContext|ValidationFailure {
		$failure = null;

		$firstResult = $first->validateInContext($validationContext);
		if ($firstResult instanceof ValidationFailure) {
			if (!$first->isScopeSafe()) {
				return $firstResult;
			}
			$failure = $firstResult;
			$nextValidationContext = $validationContext;
		} else {
			$firstExpressionType = $firstResult->expressionType;
			if ($firstExpressionType instanceof NothingType) {
				return $firstResult;
			}
			$firstBooleanType = $this->getBooleanType(
				$firstExpressionType, $origin
			);
			if ($firstBooleanType->type instanceof $cutOffType) {
				return $firstResult->withExpressionType($firstBooleanType->type);
			}
			$firstReturnType = $firstResult->returnType;
			$nextValidationContext = $firstResult;
		}
		$secondResult = $second->validateInContext($nextValidationContext);

		// Both expressions failed validation
		if ($secondResult instanceof ValidationFailure) {
			return $failure === null ? $secondResult : $failure->mergeFailure($secondResult);
		}
		// Only the first expression failed validation
		if ($failure) {
			return $failure;
		}

		$secondExpressionType = $secondResult->expressionType;
		$secondBooleanType = $this->getBooleanType(
			$secondExpressionType, $origin
		);
		$secondReturnType = $secondResult->returnType;


		if (!$this->scopeVariablesMatch(
			$firstResult->variableScope,
			$secondResult->variableScope
		)) {
			return $validationContext->withError(
				ValidationErrorType::variableScopeMismatch,
				"Variable scopes do not match between first and second expressions in boolean AND operation.",
				$origin
			);
		}

		return $this
			->contextUnion($firstResult, $secondResult)
			->withExpressionType($secondBooleanType->type)
			->withReturnType(
				$this->typeRegistry->union([
					$firstReturnType,
					$secondReturnType
				])
			);
	}

}