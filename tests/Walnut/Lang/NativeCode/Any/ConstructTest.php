<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ConstructTest extends CodeExecutionTestHelper {

	public function testConstructOpenBasic(): void {
		$result = $this->executeCodeSnippet("MyOpen('hello');",
			"MyOpen := #String;");
		$this->assertEquals("MyOpen{'hello'}", $result);
	}

	public function testConstructOpenValidatorOk(): void {
		$result = $this->executeCodeSnippet("MyOpen('hello');",
			"MyOpen := #String @ Integer :: ?when ({#->length} > 10) { => @{#->length} };");
		$this->assertEquals("MyOpen{'hello'}", $result);
	}

	public function testConstructOpenValidatorInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid constructor value",
			"MyOpen(42);",
			"MyOpen := #String;"
		);
	}

	public function testConstructOpenValidatorError(): void {
		$result = $this->executeCodeSnippet("MyOpen('hello world');",
			"MyOpen := #String @ Integer :: ?when ({#->length} > 10) { => @{#->length} };");
		$this->assertEquals("@11", $result);
	}

	public function testConstructOpenConstructorAndValidatorOk(): void {
		$result = $this->executeCodeSnippet("MyOpen(112);",
			"MyOpen := #String @ Integer :: ?when ({#->length} > 10) { => @{#->length} };v=1;" .
			"MyOpen(Integer) :: #->asString;"
		);
		$this->assertEquals("MyOpen{'112'}", $result);
	}

	/* TODO - it doesn't cover rows 57:65
	public function testConstructOpenConstructorAndValidatorInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Error in the constructor of MyOpen : Invalid return type: Real is not a open of String   ",
			"MyOpen(42);",
			"MyOpen <: String @ Integer :: ?when ({#->length} > 10) { => @{#->length} };" .
			"MyOpen(Integer) :: #->asReal;"
		);
	}
	*/

	public function testConstructOpenConstructorAndValidatorError(): void {
		$result = $this->executeCodeSnippet("MyOpen(123456789012345);",
			"MyOpen := #String @ Integer :: ?when ({#->length} > 10) { => @{#->length} };" .
			"MyOpen(Integer) :: #->asString;"
		);
		$this->assertEquals("@15", $result);
	}

	public function testConstructOpenConstructorWithErrorAndValidatorOk(): void {
		$result = $this->executeCodeSnippet("MyOpen(112);",
			"MyOpen := #String @ Integer :: ?when ({#->length} > 10) { => @{#->length} };" .
			"MyOpen(Integer) @ Boolean :: ?whenValueOf(#) is {0 : @false, ~ : #->asString};"
		);
		$this->assertEquals("MyOpen{'112'}", $result);
	}

	public function testConstructOpenConstructorWithErrorAndValidatorError(): void {
		$result = $this->executeCodeSnippet("MyOpen(0);",
			"MyOpen := #String @ Integer :: ?when ({#->length} > 10) { => @{#->length} };" .
			"MyOpen(Integer) @ Boolean :: ?whenValueOf(#) is {0 : @false, ~ : #->asString};"
		);
		$this->assertEquals("@false", $result);
	}

	public function testConstructOpenConstructorOnly(): void {
		$result = $this->executeCodeSnippet("MyOpen(112);",
			"MyOpen := #String; MyOpen(Integer) :: #->asString;"
		);
		$this->assertEquals("MyOpen{'112'}", $result);
	}



	public function testConstructSealedBasic(): void {
		$result = $this->executeCodeSnippet("MySealed[a: 'hello'];",
			"MySealed := $[a: String];");
		$this->assertEquals("MySealed[a: 'hello']", $result);
	}

	public function testConstructSealedValidatorOk(): void {
		$result = $this->executeCodeSnippet("MySealed[a: 'hello'];",
			"MySealed := $[a: String] @ Integer :: ?when ({#a->length} > 10) { => @{#a->length} };");
		$this->assertEquals("MySealed[a: 'hello']", $result);
	}

	public function testConstructSealedValidatorInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid constructor value",
			"MySealed(42);",
			"MySealed := $[a: String];"
		);
	}

	public function testConstructSealedValidatorError(): void {
		$result = $this->executeCodeSnippet("MySealed[a: 'hello world'];",
			"MySealed := $[a: String] @ Integer :: ?when ({#a->length} > 10) { => @{#a->length} };");
		$this->assertEquals("@11", $result);
	}

	public function testConstructSealedConstructorAndValidatorOk(): void {
		$result = $this->executeCodeSnippet("MySealed(112);",
			"MySealed := $[a: String] @ Integer :: ?when ({#a->length} > 10) { => @{#a->length} };" .
			"MySealed(Integer) :: [a: #->asString];"
		);
		$this->assertEquals("MySealed[a: '112']", $result);
	}

	public function testConstructSealedConstructorAndValidatorError(): void {
		$result = $this->executeCodeSnippet("MySealed(123456789012345);",
			"MySealed := $[a: String] @ Integer :: ?when ({#a->length} > 10) { => @{#a->length} };" .
			"MySealed(Integer) :: [a: #->asString];"
		);
		$this->assertEquals("@15", $result);
	}

	public function testConstructSealedConstructorWithErrorAndValidatorOk(): void {
		$result = $this->executeCodeSnippet("MySealed(112);",
			"MySealed := $[a: String] @ Integer :: ?when ({#a->length} > 10) { => @{#a->length} };" .
			"MySealed(Integer) @ Boolean :: ?whenValueOf(#) is {0 : @false, ~ : [a: #->asString]};"
		);
		$this->assertEquals("MySealed[a: '112']", $result);
	}

	public function testConstructSealedConstructorWithErrorAndValidatorError(): void {
		$result = $this->executeCodeSnippet("MySealed(0);",
			"MySealed := $[a: String] @ Integer :: ?when ({#a->length} > 10) { => @{#a->length} };" .
			"MySealed(Integer) @ Boolean :: ?whenValueOf(#) is {0 : @false, ~ : [a: #->asString]};"
		);
		$this->assertEquals("@false", $result);
	}

	public function testConstructSealedConstructorOnly(): void {
		$result = $this->executeCodeSnippet("MySealed(112);",
			"MySealed := $[a: String]; MySealed(Integer) :: [a: #->asString];"
		);
		$this->assertEquals("MySealed[a: '112']", $result);
	}

}