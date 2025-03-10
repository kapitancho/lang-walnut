<?php

namespace Walnut\Lang\Feature\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ShapeTypeTest extends CodeExecutionTestHelper {


	public function testAsStringShape(): void {
		$result = $this->executeCodeSnippet("getReal()->shape->asString;",
			"getReal = ^ => Shape<Real> :: 3.14;");
		$this->assertEquals("'3.14'", $result);
	}

	public function testAsStringShapeParam(): void {
		$result = $this->executeCodeSnippet("getValue(getReal());",
			"
				getValue = ^ r: Shape<Real> => Real :: r->shape;
				getReal = ^ => Shape<Real> :: 3.14;
			"
		);
		$this->assertEquals("3.14", $result);
	}

	public function testShapeWithCastError(): void {
		$this->executeErrorCodeSnippet(
			"Incompatible shape: String shaped as Real cannot be cast. An error value of type NotANumber is possible",
			"getValue(getString()->shaped(type{Real}));",
			"
				getString = ^ => String :: '7 days';
				getValue = ^ r: Shape<Real> => Real :: r->shape;
			"
		);
	}

	public function testAsStringShapeParamWithCast(): void {
		$result = $this->executeCodeSnippet("getReal()->shape->ceil;",
			"
				MyReal = #[value: Real];
				MyReal ==> Real :: \$value;
				getValue = ^ r: Shape<Real> => Real :: r->shape;
				getReal = ^ => Shape<Real> :: MyReal[3.14];
			"
		);
		$this->assertEquals("4", $result);
	}

	public function testShapeAsRefinedType(): void {
		$result = $this->executeCodeSnippet("useReal(getReal());",
			"
				useReal = ^p: Any => Any :: ?whenTypeOf(p) is {
					type{Shape<Real>}: p->shape->ceil,
					type{String}: p
				};
				getReal = ^ => Shape<Real> :: 3.14;
			"
		);
		$this->assertEquals("4", $result);
	}


}