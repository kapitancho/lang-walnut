<?php

namespace Walnut\Lang\Test\NativeCode\DependencyContainer;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ValueOfTest extends CodeExecutionTestHelper {

	public function testValueOf(): void {
		$result = $this->executeCodeSnippet(
			"getInteger()",
			"A := Integer; ==> A :: {A!42};",
			"getInteger = ^ => Result<Integer, DependencyContainerError> %% d: DependencyContainer :: d=>valueOf(`A)->value;"
		);
		$this->assertEquals(42, $result);
	}


	public function testValueOfNoDependencyFound(): void {
		$result = $this->executeCodeSnippet(
			"getInteger()",
			"A := Integer;",
			"getInteger = ^ => Result<Integer, DependencyContainerError> %% d: DependencyContainer :: d=>valueOf(`A)->value;"
		);
		$this->assertEquals("@DependencyContainerError![\n	targetType: type{A},\n	errorOnType: type{A},\n	errorMessage: 'Dependency not found'\n]", $result);
	}


	public function testValueOfUnsupportedType(): void {
		$result = $this->executeCodeSnippet(
			"getInteger()",
			"A := Integer;",
			"getInteger = ^ => Result<String<3>, DependencyContainerError> %% d: DependencyContainer :: d=>valueOf(`String<3>);"
		);
		$this->assertEquals("@DependencyContainerError![\n	targetType: type{String<3..3>},\n	errorOnType: type{String<3..3>},\n	errorMessage: 'Unsupported type'\n]", $result);
	}

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
