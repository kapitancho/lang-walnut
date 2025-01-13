<?php

namespace Walnut\Lang\Implementation\Function;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class CustomMethodAnalyserTest extends CodeExecutionTestHelper {

	public function testCustomMethodSignatureOk(): void {
		$result = $this->executeCodeSnippet("MyString('hello')->length", <<<NUT
			MyString <: String;
			MyString->length(=> Integer[999]) :: 999;
		NUT);
		$this->assertEquals('999', $result);
	}

	public function testCustomMethodSignatureMismatch(): void {
		$this->executeErrorCodeSnippet('the method length is already defined for String and therefore the return type Real should be a subtype of Integer', '', <<<NUT
			MyString <: String;
			MyString->length(=> Real) :: 3.14;
		NUT);
	}


	public function testCustomMethodCustomSignatureOk(): void {
		$result = $this->executeCodeSnippet("MySuperString(MyString('hello'))->realLength", <<<NUT
			MyString <: String;
			MyString->realLength(=> Real) :: 3.14;
			MySuperString <: MyString;
			MySuperString->realLength(=> Integer) :: 42;
		NUT);
		$this->assertEquals('42', $result);
	}

	public function testCustomMethodCustomSignatureMismatch(): void {
		$this->executeErrorCodeSnippet('the method realLength is already defined for MyString and therefore the signature ^Null => String should be a subtype of ^Null => Real', '', <<<NUT
			MyString <: String;
			MyString->realLength(=> Real) :: 3.14;
			MySuperString <: MyString;
			MySuperString->realLength(=> String) :: 'too long';
		NUT);
	}

	public function testCustomMethodValidatorError(): void {
		$this->executeErrorCodeSnippet('Error in the validator of MyString', '', <<<NUT
			MyString <: String :: x;
		NUT);
	}

	public function testCustomMethodConstructorError(): void {
		$this->executeErrorCodeSnippet('Error in the constructor of MyString', '', <<<NUT
			MyString <: String;
			MyString(String) :: x;
		NUT);
	}

}