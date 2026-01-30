<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Validation;

enum ValidationErrorType {
	case other;
	case typeTypeMismatch; // +
	case valueTypeMismatch; // +
	case mutableTypeMismatch;
	case undefinedVariable; // +
	case mapKeyTypeMismatch;
	case undefinedMethod; // +
	case invalidTargetType;
	case invalidParameterType; // +
	case invalidReturnType; // +
	case variableScopeMismatch;
	case dependencyNotFound;
}