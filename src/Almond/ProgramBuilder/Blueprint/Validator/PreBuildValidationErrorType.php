<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator;

enum PreBuildValidationErrorType {
	case other;
	case invalidRange;
	case nonUniqueValueDefinition;
	case missingValue;
	case missingType;
}