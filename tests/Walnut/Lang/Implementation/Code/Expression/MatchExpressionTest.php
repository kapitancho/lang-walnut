<?php

namespace Walnut\Lang\Test\Implementation\Code\Expression;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class MatchExpressionTest extends CodeExecutionTestHelper {

	public function testMatchTrue(): void {
		$result = $this->executeCodeSnippet("matchTrue(true);", valueDeclarations: <<<NUT
			matchTrue = ^Any => String['true', 'false'] :: ?whenIsTrue {
			    #: 'true',
			    ~: 'false'
			};
		NUT
		);
		$this->assertEquals("'true'", $result);
	}

	public function testMatchTrueNoMatch(): void {
		$result = $this->executeCodeSnippet("matchTrue(0);", valueDeclarations: <<<NUT
			matchTrue = ^Any => Null|String['true'] :: ?whenIsTrue {
			    #: 'true'
			};
		NUT
		);
		$this->assertEquals("null", $result);
	}

	public function testMatchType(): void {
		$result = $this->executeCodeSnippet("matchType('str');", valueDeclarations: <<<NUT
			matchType = ^Any => String :: ?whenTypeOf(#) {
			    type{String}: 'string',
			    ~: 'not a string'
			};
		NUT
		);
		$this->assertEquals("'string'", $result);
	}

	public function testMatchTypeWithDynamicTypes(): void {
		$result = $this->executeCodeSnippet("matchType('str');", valueDeclarations: <<<NUT
			matchType = ^Any => String :: ?whenTypeOf(#) {
			    #->type: 'string',
			    ~: 'not a string'
			};
		NUT
		);
		$this->assertEquals("'string'", $result);
	}

	public function testMatchValue(): void {
		$result = $this->executeCodeSnippet("matchValue('hello');", valueDeclarations: <<<NUT
			matchValue = ^Any => String :: ?whenValueOf(#) {
			    'hello': 'hello',
			    ~: 'not hello'
			};			
		NUT
		);
		$this->assertEquals("'hello'", $result);
	}

	public function testMatchIf(): void {
		$result = $this->executeCodeSnippet("matchIf('yes');", valueDeclarations: <<<NUT
			matchIf = ^Any => String|Null :: ?when(#) {
			    'true'
			};
		NUT
		);
		$this->assertEquals("'true'", $result);
	}

	public function testMatchIfNoMatch(): void {
		$result = $this->executeCodeSnippet("matchIf('');", valueDeclarations: <<<NUT
			matchIf = ^Any => String|Null :: ?when(#) {
			    'true'
			};
		NUT
		);
		$this->assertEquals("null", $result);
	}

	public function testMatchIfWithElse(): void {
		$result = $this->executeCodeSnippet("matchIfWithElse('');", valueDeclarations: <<<NUT
			matchIfWithElse = ^Any => String|Null :: ?when(#) {
			    'true'
			} ~ {
			    'false'
			};
		NUT
		);
		$this->assertEquals("'false'", $result);
	}

}