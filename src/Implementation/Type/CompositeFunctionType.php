<?php

namespace Walnut\Lang\Implementation\Type;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\AnyType;
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
		$exe = $this->typeRegistry->core->externalError;

		$this->parameterType = $this->first->parameterType;
		$r = $this->first->returnType;

		$fReturn = match(true) {
			$r instanceof ResultType => $r->returnType,
			default => $r,
		};
		$fError = match(true) {
			$r instanceof AnyType => $r,
			$r instanceof ResultType => $r->errorType,
			default => null,
		};

		if (!$fError && $this->compositionMode === FunctionCompositionMode::orElse) {
			$this->returnType = $r;
			return;
		}

		$firstCheckType = match($this->compositionMode) {
			FunctionCompositionMode::orElse => $this->parameterType,
			FunctionCompositionMode::direct => $r,
			FunctionCompositionMode::bypassErrors => $fReturn,
			FunctionCompositionMode::bypassExternalErrors => $fError && $exe->isSubtypeOf($fError) ?
				$this->withoutExternalError($this->typeRegistry, $r) : $r
		};

		$firstReturnType = match($this->compositionMode) {
			FunctionCompositionMode::orElse => $fReturn,
			default => null,
		};
		$firstErrorType = match($this->compositionMode) {
			FunctionCompositionMode::orElse, FunctionCompositionMode::direct => null,
			FunctionCompositionMode::bypassErrors => $fError,
			FunctionCompositionMode::bypassExternalErrors => $fError && $exe->isSubtypeOf($fError) ? $exe : null,
		};
		if (!$firstCheckType->isSubtypeOf($this->second->parameterType)) {
			throw new AnalyserException(
				sprintf(
					"Cannot compose functions: %s type %s of first function is not a subtype of parameter type %s of second function",
					$this->compositionMode === FunctionCompositionMode::orElse ? 'parameter' : 'return',
					$firstCheckType,
					$this->second->parameterType
				)
			);
		}
		$returnType = $firstReturnType ? $this->typeRegistry->union([
			$firstReturnType,
			$this->second->returnType
		]) : $this->second->returnType;

		$this->returnType = $firstErrorType ?
			$this->typeRegistry->result(
				$returnType,
				$firstErrorType
			) :
			$returnType;
	}

	public function composeWith(FunctionType $nextFunctionType, FunctionCompositionMode $compositionMode): FunctionType {
		return new CompositeFunctionType(
			$this->typeRegistry,
			$this,
			$nextFunctionType,
			$compositionMode
		);
	}

	// @codeCoverageIgnoreStart
	public function __toString(): string {
		return md5($this->first . ' + ' . $this->second);
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
	// @codeCoverageIgnoreEnd
}