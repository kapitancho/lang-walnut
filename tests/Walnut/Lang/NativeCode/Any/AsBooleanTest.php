<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Test\CodeExecutionTestHelper;

// ESSENTIAL TEST
final class AsBooleanTest extends CodeExecutionTestHelper {

	public function testAsBooleanNull(): void {
		$result = $this->executeCodeSnippet("null->asBoolean;");
		$this->assertEquals("false", $result);
	}

	public function testAsBooleanTrue(): void {
		$result = $this->executeCodeSnippet("true->asBoolean;");
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanFalse(): void {
		$result = $this->executeCodeSnippet("false->asBoolean;");
		$this->assertEquals("false", $result);
	}

	public function testAsBooleanIntegerFalse(): void {
		$result = $this->executeCodeSnippet("0->asBoolean;");
		$this->assertEquals("false", $result);
	}

	public function testAsBooleanIntegerTrue(): void {
		$result = $this->executeCodeSnippet("5->asBoolean;");
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanRealFalse(): void {
		$result = $this->executeCodeSnippet("0.0->asBoolean;");
		$this->assertEquals("false", $result);
	}

	public function testAsBooleanRealTrue(): void {
		$result = $this->executeCodeSnippet("5.5->asBoolean;");
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanStringFalse(): void {
		$result = $this->executeCodeSnippet("''->asBoolean;");
		$this->assertEquals("false", $result);
	}

	public function testAsBooleanStringTrue(): void {
		$result = $this->executeCodeSnippet("'Hello'->asBoolean;");
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanTupleFalse(): void {
		$result = $this->executeCodeSnippet("[]->asBoolean;");
		$this->assertEquals("false", $result);
	}

	public function testAsBooleanTupleTrue(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3]->asBoolean;");
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanRecordFalse(): void {
		$result = $this->executeCodeSnippet("[:]->asBoolean;");
		$this->assertEquals("false", $result);
	}

	public function testAsBooleanRecordTrue(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2]->asBoolean;");
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanSetFalse(): void {
		$result = $this->executeCodeSnippet("[;]->asBoolean;");
		$this->assertEquals("false", $result);
	}

	public function testAsBooleanSetTrue(): void {
		$result = $this->executeCodeSnippet("[1; 2; 3]->asBoolean;");
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanError(): void {
		$result = $this->executeCodeSnippet("{@0}->asBoolean;");
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanAtom(): void {
		$result = $this->executeCodeSnippet("{MyAtom}->asBoolean;", "MyAtom := ();");
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanEnumeration(): void {
		$result = $this->executeCodeSnippet("{MyEnumeration.C}->asBoolean;", "MyEnumeration := (A, B, C);");
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanOpen(): void {
		$result = $this->executeCodeSnippet("{MyOpen('value')}->asBoolean;", "MyOpen := #String;");
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanSealed(): void {
		$result = $this->executeCodeSnippet("{MySealed[a: 'value']}->asBoolean;", "MySealed := $[a: String];");
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanAliasFalse(): void {
		$result = $this->executeCodeSnippet("{getMyAlias()}->asBoolean;",
			"MyAlias = Integer;", "getMyAlias = ^Any => MyAlias :: 0;");
		$this->assertEquals("false", $result);
	}

	public function testAsBooleanAliasTrue(): void {
		$result = $this->executeCodeSnippet("{getMyAlias()}->asBoolean;",
			"MyAlias = Integer;", "getMyAlias = ^Any => MyAlias :: 1;");
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanFunction(): void {
		$result = $this->executeCodeSnippet("{^Any => Integer :: 1}->asBoolean;");
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanMutableFalse(): void {
		$result = $this->executeCodeSnippet("{mutable{Integer, 0}}->asBoolean;");
		$this->assertEquals("false", $result);
	}

	public function testAsBooleanMutableTrue(): void {
		$result = $this->executeCodeSnippet("{mutable{Integer, 1}}->asBoolean;");
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanShapeFalse(): void {
		$result = $this->executeCodeSnippet("getReal()->shape(`Real)->asBoolean;",
			valueDeclarations: "getReal = ^ => Shape<Real> :: 0;");
		$this->assertEquals("false", $result);
	}

	public function testAsBooleanShapeTrue(): void {
		$result = $this->executeCodeSnippet("getReal()->shape(`Real)->asBoolean;",
			valueDeclarations: "getReal = ^ => Shape<Real> :: 3.14;");
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanType(): void {
		$result = $this->executeCodeSnippet("type{Integer}->asBoolean;");
		$this->assertEquals("true", $result);
	}

}