<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\DependencyContainer;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ValueOfTest extends CodeExecutionTestHelper {

	public function testValueOf(): void {
		$result = $this->executeCodeSnippet(
			"getInteger()",
			"A := Integer; ==> A :: A!42;",
			"getInteger = ^ => Result<Integer, DependencyContainerError> %% d: DependencyContainer :: d->valueOf(`A)?->value;"
		);
		$this->assertEquals(42, $result);
	}


	public function testValueOfNoDependencyFound(): void {
		$result = $this->executeCodeSnippet(
			"getInteger()",
			"A := Integer;",
			"getInteger = ^ => Result<Integer, DependencyContainerError> %% d: DependencyContainer :: d->valueOf(`A)?->value;"
		);
		$this->assertEquals("@DependencyContainerError![\n	targetType: type{A},\n	errorOnType: type{A},\n	errorMessage: 'Dependency not found: Method \`asA\` is not defined for type \`DependencyContainer\`.'\n]", $result);
	}


	public function testValueOfUnsupportedType(): void {
		$result = $this->executeCodeSnippet(
			"getInteger()",
			"A := Integer;",
			"getInteger = ^ => Result<String<3>, DependencyContainerError> %% d: DependencyContainer :: d->valueOf(`String<3>)?;"
		);
		$this->assertEquals("@DependencyContainerError![\n	targetType: type{String<3>},\n	errorOnType: type{String<3>},\n	errorMessage: 'Unsupported type'\n]", $result);
	}

	public function testValueOfWithInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			'Invalid parameter type',
			"DependencyContainer->valueOf['not a type'];",
		);
	}

}
