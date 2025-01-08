<?php

namespace Walnut\Lang\Implementation\Function;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Function\CustomMethodDraft as CustomMethodDraftInterface;
use Walnut\Lang\Blueprint\Function\FunctionBodyDraft;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class CustomMethodDraft implements CustomMethodDraftInterface, JsonSerializable {
	public function __construct(
		public Type $targetType,
		public MethodNameIdentifier $methodName,
		public Type $parameterType,
		public Type $dependencyType,
		public Type $returnType,
		public FunctionBodyDraft $functionBody,
	) {}

	/** @throws AnalyserException */
	public function analyse(
		Type $targetType,
		Type $parameterType
	): Type {
		return $this->returnType;
	}

	public function __toString(): string {
		$dependency = $this->dependencyType ?
			sprintf(" using %s", $this->dependencyType) : '';
		return sprintf(
			"%s:%s ^%s => %s%s :: %s",
			$this->targetType,
			$this->methodName,
			$this->parameterType,
			$this->returnType,
			$dependency,
			$this->functionBody
		);
	}

	public function jsonSerialize(): array {
		return [
			'targetType' => (string)$this->targetType,
			'methodName' => $this->methodName,
			'parameterType' => $this->parameterType,
			'dependencyType' => $this->dependencyType,
			'returnType' => $this->returnType,
			'functionBody' => $this->functionBody
		];
	}
}