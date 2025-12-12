<?php

namespace Walnut\Lang\Blueprint\Value;

enum FunctionCompositionMode {
	case direct;
	case bypassErrors;
	case bypassExternalErrors;
}