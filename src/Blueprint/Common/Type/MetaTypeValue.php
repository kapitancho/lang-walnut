<?php

namespace Walnut\Lang\Blueprint\Common\Type;

enum MetaTypeValue: string {
    case Function = 'Function';
	case Tuple = 'Tuple';
	case Record = 'Record';
	case Union = 'Union';
	case Intersection = 'Intersection';
	case Atom = 'Atom';
	case Enumeration = 'Enumeration';
	case EnumerationSubset = 'EnumerationSubset';
	case EnumerationValue = 'EnumerationValue';
	case IntegerSubset = 'IntegerSubset';
	case MutableValue = 'MutableValue';
	case RealSubset = 'RealSubset';
	case StringSubset = 'StringSubset';
	case Alias = 'Alias';
	case Open = 'Open';
	case Subset = 'Subset';
	case Subtype = 'Subtype';
	case Sealed = 'Sealed';
	case Named = 'Named';
}