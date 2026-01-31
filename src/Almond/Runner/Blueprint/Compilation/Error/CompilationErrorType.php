<?php

namespace Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error;

enum CompilationErrorType {
	case other;
	case parseError;
	case buildError;
	case moduleDependencyMissing;
	case moduleDependencyLoop;

	case typeTypeMismatch;
	case valueTypeMismatch;
	case mutableTypeMismatch;
	case undefinedVariable;
	case mapKeyTypeMismatch;
	case undefinedMethod;
	case invalidTargetType;
	case invalidParameterType;
	case invalidReturnType;

	case dependencyNotFound;

	case invalidRange;
	case nonUniqueValueDefinition;
	case missingType;
	case missingValue;
	case variableScopeMismatch;
	case executionError;
}