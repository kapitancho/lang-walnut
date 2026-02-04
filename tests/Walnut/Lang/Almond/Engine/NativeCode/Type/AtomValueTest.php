<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AtomValueTest extends CodeExecutionTestHelper {

	public function testValueAtom(): void {
		$result = $this->executeCodeSnippet("type{MyAtom}->atomValue;", "MyAtom := ();");
		$this->assertEquals("MyAtom", $result);
	}

	public function testValuesEnumerationMetaType(): void {
		$result = $this->executeCodeSnippet(
			"getAtomValue(type{MyAtom});",
			"MyAtom := ();",
			"getAtomValue = ^Type<Atom> => Any :: #->atomValue;"
		);
		$this->assertEquals("MyAtom", $result);
	}

	public function testValuesInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type',
			"type{String}->atomValue;");
	}

	public function testValuesInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"type{MyAtom}->atomValue(42);",
			"MyAtom := ();"
		);
	}

}