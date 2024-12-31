<?php

namespace Walnut\Lang\Implementation\NativeCode\Real;

use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class LnTest extends BaseProgramTestHelper {

	private function callLn(Value $value, float $expected): void {
		$this->testMethodCall(
			$this->expressionRegistry->constant($value),
			'ln',
			$this->expressionRegistry->constant($this->valueRegistry->null),
			$this->valueRegistry->real($expected)
		);
	}

	private function analyseCallLn(Type $type, Type $expected): void {
        $this->testMethodCallAnalyse(
            $type,
            'ln',
            $this->typeRegistry->null,
            $expected
        );
	}

	public function testLn(): void {
		$this->callLn($this->valueRegistry->real(1), 0);
		$this->callLn($this->valueRegistry->real(2.718281828459045), 1);

		$this->typeRegistry->addAlias(
			new TypeNameIdentifier('PositiveReal'),
			$this->typeRegistry->real(0)
		);
		$this->typeRegistry->addSubtype(
			new TypeNameIdentifier('MyReal'),
			$this->typeRegistry->withName(new TypeNameIdentifier('PositiveReal')),
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->constant($this->valueRegistry->null)
			),
			null
		);
		$this->typeRegistry->addAtom(new TypeNameIdentifier('NotANumber'));
		$this->callLn($this->valueRegistry->subtypeValue(
			new TypeNameIdentifier('MyReal'),
			$this->valueRegistry->real(1)
		), 0);

        $this->testMethodCall(
            $this->expressionRegistry->constant(
                $this->valueRegistry->real(0)
            ),
            'ln',
            $this->expressionRegistry->constant(
                $this->valueRegistry->null),
            $this->valueRegistry->error(
                $this->valueRegistry->atom(
                    new TypeNameIdentifier('NotANumber')
                )
            )
        );

        $this->analyseCallLn(
            $this->typeRegistry->real(3.14, 99.9),
            $this->typeRegistry->real(max: 99.9)
        );
        $this->analyseCallLn(
            $this->typeRegistry->real(-3.14, 99.9),
            $this->typeRegistry->result(
                $this->typeRegistry->real(max: 99.9),
                $this->typeRegistry->atom(
                    new TypeNameIdentifier('NotANumber')
                )
            )
        );
	}
}
