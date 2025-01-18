<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ConstructTest extends CodeExecutionTestHelper {

	public function testConstructSubtypeBasic(): void {
		$result = $this->executeCodeSnippet("MySubtype('hello');",
			"MySubtype <: String;");
		$this->assertEquals("MySubtype{'hello'}", $result);
	}

	public function testConstructSubtypeValidatorOk(): void {
		$result = $this->executeCodeSnippet("MySubtype('hello');",
			"MySubtype <: String @ Integer :: ?when ({#->length} > 10) { => @{#->length} };");
		$this->assertEquals("MySubtype{'hello'}", $result);
	}

	public function testConstructSubtypeValidatorInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid constructor value",
			"MySubtype(42);",
			"MySubtype <: String;"
		);
	}

	public function testConstructSubtypeValidatorError(): void {
		$result = $this->executeCodeSnippet("MySubtype('hello world');",
			"MySubtype <: String @ Integer :: ?when ({#->length} > 10) { => @{#->length} };");
		$this->assertEquals("@11", $result);
	}

	public function testConstructSubtypeConstructorAndValidatorOk(): void {
		$result = $this->executeCodeSnippet("MySubtype(112);",
			"MySubtype <: String @ Integer :: ?when ({#->length} > 10) { => @{#->length} };" .
			"MySubtype(Integer) :: #->asString;"
		);
		$this->assertEquals("MySubtype{'112'}", $result);
	}

	/* TODO - it doesn't cover rows 57:65
	public function testConstructSubtypeConstructorAndValidatorInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Error in the constructor of MySubtype : Invalid return type: Real is not a subtype of String   ",
			"MySubtype(42);",
			"MySubtype <: String @ Integer :: ?when ({#->length} > 10) { => @{#->length} };" .
			"MySubtype(Integer) :: #->asReal;"
		);
	}
	*/

	public function testConstructSubtypeConstructorAndValidatorError(): void {
		$result = $this->executeCodeSnippet("MySubtype(123456789012345);",
			"MySubtype <: String @ Integer :: ?when ({#->length} > 10) { => @{#->length} };" .
			"MySubtype(Integer) :: #->asString;"
		);
		$this->assertEquals("@15", $result);
	}

	public function testConstructSubtypeConstructorWithErrorAndValidatorOk(): void {
		$result = $this->executeCodeSnippet("MySubtype(112);",
			"MySubtype <: String @ Integer :: ?when ({#->length} > 10) { => @{#->length} };" .
			"MySubtype(Integer) @ Boolean :: ?whenValueOf(#) is {0 : @false, ~ : #->asString};"
		);
		$this->assertEquals("MySubtype{'112'}", $result);
	}

	public function testConstructSubtypeConstructorWithErrorAndValidatorError(): void {
		$result = $this->executeCodeSnippet("MySubtype(0);",
			"MySubtype <: String @ Integer :: ?when ({#->length} > 10) { => @{#->length} };" .
			"MySubtype(Integer) @ Boolean :: ?whenValueOf(#) is {0 : @false, ~ : #->asString};"
		);
		$this->assertEquals("@false", $result);
	}

	public function testConstructSubtypeConstructorOnly(): void {
		$result = $this->executeCodeSnippet("MySubtype(112);",
			"MySubtype <: String; MySubtype(Integer) :: #->asString;"
		);
		$this->assertEquals("MySubtype{'112'}", $result);
	}



	public function testConstructSealedBasic(): void {
		$result = $this->executeCodeSnippet("MySealed[a: 'hello'];",
			"MySealed <: [a: String];");
		$this->assertEquals("MySealed[a: 'hello']", $result);
	}

	public function testConstructSealedValidatorOk(): void {
		$result = $this->executeCodeSnippet("MySealed[a: 'hello'];",
			"MySealed <: [a: String] @ Integer :: ?when ({#a->length} > 10) { => @{#a->length} };");
		$this->assertEquals("MySealed[a: 'hello']", $result);
	}

	public function testConstructSealedValidatorInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid constructor value",
			"MySealed(42);",
			"MySealed <: [a: String];"
		);
	}

	public function testConstructSealedValidatorError(): void {
		$result = $this->executeCodeSnippet("MySealed[a: 'hello world'];",
			"MySealed <: [a: String] @ Integer :: ?when ({#a->length} > 10) { => @{#a->length} };");
		$this->assertEquals("@11", $result);
	}

	public function testConstructSealedConstructorAndValidatorOk(): void {
		$result = $this->executeCodeSnippet("MySealed(112);",
			"MySealed <: [a: String] @ Integer :: ?when ({#a->length} > 10) { => @{#a->length} };" .
			"MySealed(Integer) :: [a: #->asString];"
		);
		$this->assertEquals("MySealed[a: '112']", $result);
	}

	public function testConstructSealedConstructorAndValidatorError(): void {
		$result = $this->executeCodeSnippet("MySealed(123456789012345);",
			"MySealed <: [a: String] @ Integer :: ?when ({#a->length} > 10) { => @{#a->length} };" .
			"MySealed(Integer) :: [a: #->asString];"
		);
		$this->assertEquals("@15", $result);
	}

	public function testConstructSealedConstructorWithErrorAndValidatorOk(): void {
		$result = $this->executeCodeSnippet("MySealed(112);",
			"MySealed <: [a: String] @ Integer :: ?when ({#a->length} > 10) { => @{#a->length} };" .
			"MySealed(Integer) @ Boolean :: ?whenValueOf(#) is {0 : @false, ~ : [a: #->asString]};"
		);
		$this->assertEquals("MySealed[a: '112']", $result);
	}

	public function testConstructSealedConstructorWithErrorAndValidatorError(): void {
		$result = $this->executeCodeSnippet("MySealed(0);",
			"MySealed <: [a: String] @ Integer :: ?when ({#a->length} > 10) { => @{#a->length} };" .
			"MySealed(Integer) @ Boolean :: ?whenValueOf(#) is {0 : @false, ~ : [a: #->asString]};"
		);
		$this->assertEquals("@false", $result);
	}

	public function testConstructSealedConstructorOnly(): void {
		$result = $this->executeCodeSnippet("MySealed(112);",
			"MySealed <: [a: String]; MySealed(Integer) :: [a: #->asString];"
		);
		$this->assertEquals("MySealed[a: '112']", $result);
	}

}