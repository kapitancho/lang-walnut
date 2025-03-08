module $http/core:

HttpProtocolVersion = :[HTTP1, HTTP11, HTTP2, HTTP3];
InvalidHttpProtocolVersion = $[value: String];

String ==> HttpProtocolVersion @ InvalidHttpProtocolVersion :: {
    ?whenValueOf($) is {
        '1.0': HttpProtocolVersion.HTTP1,
        '1.1': HttpProtocolVersion.HTTP11,
        '2.0': HttpProtocolVersion.HTTP2,
        '3.0': HttpProtocolVersion.HTTP3,
        ~: Error(InvalidHttpProtocolVersion[$])
    }
};

HttpHeadersList = Map<Array<String, 1..>>;
HttpMessageBody = String;

HttpStatusCode = Integer[
    100, 101, 102, 103,
    200, 201, 202, 203, 204, 205, 206, 207, 208, 226,
    300, 301, 302, 303, 304, 307, 308,
    400, 401, 402, 403, 404, 405, 406, 407, 408, 409,
    410, 411, 412, 413, 414, 415, 416, 417, 418, 421,
    422, 423, 424, 425, 426, 428, 429, 431, 451,
    500, 501, 502, 503, 504, 505, 506, 507, 508, 510, 511
];
HttpHeaderName = String;
HttpRequestTarget = String;
HttpRequestMethod = :[CONNECT, DELETE, GET, HEAD, OPTIONS, PATCH, POST, PUT, TRACE];

HttpRequest = [
    protocolVersion: HttpProtocolVersion,
    headers: HttpHeadersList,
    body: HttpMessageBody|Null,
    requestTarget: HttpRequestTarget,
    method: HttpRequestMethod
];

HttpResponse = [
    protocolVersion: HttpProtocolVersion,
    headers: HttpHeadersList,
    body: HttpMessageBody|Null,
    statusCode: HttpStatusCode
];
HttpResponse->withHeader(^[headerName: String, values: Array<String, 1..>] => HttpResponse) :: {
    [
        protocolVersion: $protocolVersion,
        body: $body,
        headers: $headers->withKeyValue[key: #headerName, value: #values],
        statusCode: $statusCode
    ]
};
HttpResponse->withBody(^body: HttpMessageBody|Null => HttpResponse) :: [
    protocolVersion: $protocolVersion,
    body: body,
    headers: $headers,
    statusCode: $statusCode
];

HttpRequestHandler = ^[request: HttpRequest] => Result<HttpResponse, Any>;
HttpMiddleware = ^[request: HttpRequest, handler: HttpRequestHandler] => Result<HttpResponse, Any>;

CreateHttpResponse = ^HttpStatusCode => HttpResponse;

==> CreateHttpResponse :: ^code: HttpStatusCode => HttpResponse :: [
    statusCode: code,
    protocolVersion: HttpProtocolVersion.HTTP11,
    headers: [:],
    body: ''
];
