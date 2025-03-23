<?php

namespace Walnut\Lang\Test;

use Walnut\Lang\Blueprint\Compilation\AST\AstProgramCompilationException;

final class CodeExecutionTest extends CodeExecutionTestHelper {

	public function testCodeExecutionOk(): void {
		$result = $this->executeCodeSnippet('{#=>item(0)=>asInteger} + {#=>item(1)=>asInteger};', parameters: [2, 3]);
		$this->assertEquals('5', $result);
	}

	public function testCodeExecutionContextOk(): void {
		$result = $this->executeCodeSnippet('{#=>item(0)=>asInteger} + getConst();', 'getConst = ^Any => Integer :: 7;', [5]);
		$this->assertEquals('12', $result);
	}

	public function testCodeExecutionCompilationValueUnknownType(): void {
		$this->expectException(AstProgramCompilationException::class);
		$this->executeCodeSnippet('MyAtom();');
	}

	public function testCodeExecutionCompilationValueUnknownEnumerationValue(): void {
		$this->expectException(AstProgramCompilationException::class);
		$this->executeCodeSnippet('MyEnum.X;', 'MyEnum = :[A, B, C];');
	}

	public function testCodeExecutionCompilationValueUnknownEnumerationType(): void {
		$this->expectException(AstProgramCompilationException::class);
		$this->executeCodeSnippet('MyEnum.X;');
	}

	public function testCodeExecutionCompilationTypeUnknownEnumerationValue(): void {
		$this->expectException(AstProgramCompilationException::class);
		$this->executeCodeSnippet('type{MyEnum[X]};', 'MyEnum = :[A, B, C];');
	}

	public function testCodeExecutionCompilationUnknownType(): void {
		$this->expectException(AstProgramCompilationException::class);
		$this->executeCodeSnippet('type{MyAtom};');
	}

	public function testCodeExecutionCompilationInvalidRange(): void {
		$this->expectException(AstProgramCompilationException::class);
		$this->executeCodeSnippet('type{Integer<3..-2>};');
	}

	public function testCodeExecutionCompilationConstructorUnknownType(): void {
		$this->expectException(AstProgramCompilationException::class);
		$this->executeCodeSnippet('null;', 'MyType(Null) :: null;');
	}

}