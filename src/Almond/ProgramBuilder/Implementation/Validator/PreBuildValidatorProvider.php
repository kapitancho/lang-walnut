<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator;

use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidatorProvider as PreBuildValidatorProviderInterface;
use Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator\Range\ArrayLengthRangeValidator;
use Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator\Range\IntegerRangeValidator;
use Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator\Range\MapLengthRangeValidator;
use Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator\Range\NumberIntervalValidator;
use Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator\Range\RealRangeValidator;
use Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator\Range\SetLengthRangeValidator;
use Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator\Type\DuplicateTypeNameValidator;
use Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator\Type\EnumerationSubsetTypeValidator;
use Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator\Type\EnumerationTypeValidator;
use Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator\Type\IntegerSubsetValidator;
use Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator\Type\RealSubsetValidator;
use Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator\Type\StringSubsetValidator;
use Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator\Type\TypeNameExistsValidator;
use Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator\Value\AtomValueTypeExistsValidator;
use Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator\Value\DataTypeExistsValidator;
use Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator\Value\EnumerationValueExistsValidator;

final class PreBuildValidatorProvider implements PreBuildValidatorProviderInterface {

	public array $validators {
		get {
			return [
				new DuplicateTypeNameValidator(),
				new AtomValueTypeExistsValidator(),
				new DataTypeExistsValidator(),
				new EnumerationTypeValidator(),
				new EnumerationSubsetTypeValidator(),
				new EnumerationValueExistsValidator(),
				new ArrayLengthRangeValidator(),
				new MapLengthRangeValidator(),
				new SetLengthRangeValidator(),
				new RealRangeValidator(),
				new IntegerRangeValidator(),
				new NumberIntervalValidator(),
				new TypeNameExistsValidator(),
				new StringSubsetValidator(),
				new RealSubsetValidator(),
				new IntegerSubsetValidator(),
			];
		}
	}
}