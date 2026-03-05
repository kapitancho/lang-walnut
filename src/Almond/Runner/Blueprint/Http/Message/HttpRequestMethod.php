<?php

namespace Walnut\Lang\Almond\Runner\Blueprint\Http\Message;

enum HttpRequestMethod: string {
	case get = 'GET';
	case post = 'POST';
	case put = 'PUT';
	case patch = 'PATCH';
	case delete = 'DELETE';
	case head = 'HEAD';
	case options = 'OPTIONS';
	case connect = 'CONNECT';
	case trace = 'TRACE';
}