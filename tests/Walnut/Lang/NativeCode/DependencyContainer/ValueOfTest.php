<?php

namespace Walnut\Lang\Test\NativeCode\DependencyContainer;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ValueOfTest extends CodeExecutionTestHelper {

	public function testValueOfWithInvalidTargetType(): void {
		$this->executeErrorCodeSnippet(
			'Cannot call method',
			"'not a container'->valueOf[\`String];",
			typeDeclarations: "
				DependencyContainer := ();
				CircularDependency := ();
				Ambiguous := ();
				NotFound := ();
				UnsupportedType := ();
				ErrorWhileCreatingValue := ();
				DependencyContainerErrorType := (CircularDependency, Ambiguous, NotFound, UnsupportedType, ErrorWhileCreatingValue);
				DependencyContainerError := [targetType: Type, errorOnType: Type, errorMessage: String, errorType: DependencyContainerErrorType];
			"
		);
	}

	public function testValueOfWithInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			'Invalid parameter type',
			"DependencyContainer->valueOf['not a type'];",
			typeDeclarations: "
				DependencyContainer := ();
				CircularDependency := ();
				Ambiguous := ();
				NotFound := ();
				UnsupportedType := ();
				ErrorWhileCreatingValue := ();
				DependencyContainerErrorType := (CircularDependency, Ambiguous, NotFound, UnsupportedType, ErrorWhileCreatingValue);
				DependencyContainerError := [targetType: Type, errorOnType: Type, errorMessage: String, errorType: DependencyContainerErrorType];
			"
		);
	}

}
