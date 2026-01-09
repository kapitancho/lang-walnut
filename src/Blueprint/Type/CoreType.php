<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;

enum CoreType {
	case CannotFormatString;
	case CastNotAvailable;
	case CliEntryPoint;
	case Constructor;
	case DependencyContainer;
	case DependencyContainerError;
	case DependencyContainerErrorType;
	case ExternalError;
	case HydrationError;
	case IndexOutOfRange;
	case IntegerRange;
	case IntegerNumberIntervalEndpoint;
	case IntegerNumberInterval;
	case IntegerNumberRange;
	case InvalidIntegerRange;
	case InvalidLengthRange;
	case InvalidJsonString;
	case InvalidJsonValue;
	case InvalidRealRange;
	case InvalidRegExp;
	case InvalidUuid;
	case InvocationError;
	case ItemNotFound;
	case JsonValue;
	case LengthRange;
	case MapItemNotFound;
	case MinusInfinity;
	case NegativeInteger;
	case NegativeReal;
	case NonEmptyString;
	case NonNegativeInteger;
	case NonNegativeReal;
	case NonPositiveInteger;
	case NonPositiveReal;
	case NoRegExpMatch;
	case NonZeroInteger;
	case NonZeroReal;
	case NotANumber;
	case PasswordString;
	case PlusInfinity;
	case PositiveInteger;
	case PositiveReal;
	case Random;
	case RealNumberIntervalEndpoint;
	case RealNumberInterval;
	case RealNumberRange;
	case RealRange;
	case RegExp;
	case RegExpMatch;
	case SliceNotInBytes;
	case SubstringNotInString;
	case UnknownEnumerationValue;
	case Uuid;

	public function typeName(): TypeNameIdentifier {
		return new TypeNameIdentifier($this->name);
	}
}