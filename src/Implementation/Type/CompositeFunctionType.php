<?php

namespace Walnut\Lang\Implementation\Type;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\FunctionType as FunctionTypeInterface;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\SupertypeChecker;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\FunctionCompositionMode;
use Walnut\Lang\Implementation\Type\Helper\ExternalTypeHelper;

final readonly class CompositeFunctionType implements FunctionType {
	use ExternalTypeHelper;

	public Type $parameterType;
	public Type $returnType;

	public function __construct(
		private TypeRegistry $typeRegistry,
		private FunctionType $first,
		private FunctionType $second,
		private FunctionCompositionMode $compositionMode
	) {
		$exe = $this->typeRegistry->withName(new TypeNameIdentifier('ExternalError'));

		$this->parameterType = $this->first->parameterType;
		$r = $this->first->returnType;

		$firstReturnType = match($this->compositionMode) {
			FunctionCompositionMode::direct => $r,
			FunctionCompositionMode::bypassErrors =>
				$r instanceof ResultType ? $r->returnType : $r,
			FunctionCompositionMode::bypassExternalErrors =>
				$r instanceof ResultType && $exe->isSubtypeOf($r->errorType) ?
					$this->withoutExternalError($this->typeRegistry, $r) :
					$this->first->returnType,

		};
		$firstErrorType = match($this->compositionMode) {
			FunctionCompositionMode::direct => null,
			FunctionCompositionMode::bypassErrors =>
				$this->first->returnType instanceof ResultType ?
					$this->first->returnType->errorType :
					null,
			FunctionCompositionMode::bypassExternalErrors =>
				$this->first->returnType instanceof ResultType &&
				$this->first->returnType->errorType->isSubtypeOf($exe) ? $exe : null,
		};

		if (!$firstReturnType->isSubtypeOf($this->second->parameterType)) {
			throw new AnalyserException(
				sprintf(
					"Cannot compose functions: return type %s of first function is not a subtype of parameter type %s of second function",
					$firstReturnType,
					$this->second->parameterType
				)
			);
		}
		$this->returnType = $firstErrorType ?
			$this->typeRegistry->result(
				$this->second->returnType,
				$firstErrorType
			) :
			$this->second->returnType;
	}

	public function __toString(): string {
		return md5($this->first . ' + ' . $this->second);
	}

	public function composeWith(FunctionType $nextFunctionType, FunctionCompositionMode $compositionMode): FunctionType {
		return new CompositeFunctionType(
			$this->typeRegistry,
			$this,
			$nextFunctionType,
			$compositionMode
		);
	}

	public function isSubtypeOf(Type $ofType): bool {
		return match(true) {
			$ofType instanceof FunctionTypeInterface =>
				$ofType->parameterType->isSubtypeOf($this->parameterType) &&
				$this->returnType->isSubtypeOf($ofType->returnType),
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
			default => false
		};
	}
}