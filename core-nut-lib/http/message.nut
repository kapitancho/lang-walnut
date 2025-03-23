module $http/message:

HttpProtocolVersion = :[http_1_0, http_1_1, http_2, http_3];
HttpRequestMethod = :[connect, delete, get, head, options, patch, post, put, trace];
HttpRequestTarget = String;
HttpResponseStatusCode = Integer[
    100, 101, 102, 103,
    200, 201, 202, 203, 204, 205, 206, 207, 208, 226,
    300, 301, 302, 303, 304, 307, 308,
    400, 401, 402, 403, 404, 405, 406, 407, 408, 409,
    410, 411, 412, 413, 414, 415, 416, 417, 418, 421,
    422, 423, 424, 425, 426, 428, 429, 431, 451,
    500, 501, 502, 503, 504, 505, 506, 507, 508, 510, 511
];
HttpHeaders = Map<Array<String, 1..>>;
HttpMessageBody = String|Null;

HttpRequest = [
    protocolVersion: HttpProtocolVersion,
    method: HttpRequestMethod,
    target: HttpRequestTarget,
    headers: HttpHeaders,
    body: HttpMessageBody
];
HttpResponse = [
    protocolVersion: HttpProtocolVersion,
    statusCode: HttpResponseStatusCode,
    headers: HttpHeaders,
    body: HttpMessageBody
];

HttpResponseBuilder = ^HttpResponseStatusCode => HttpResponse;
==> HttpResponseBuilder :: ^code: HttpResponseStatusCode => HttpResponse :: [
    statusCode: code,
    protocolVersion: HttpProtocolVersion.http_1_1,
    headers: [:],
    body: null
];

HttpResponse->withHeader(^[headerName: String, values: Array<String, 1..>] => HttpResponse) :: {
    [
        protocolVersion: $protocolVersion,
        body: $body,
        headers: $headers->withKeyValue[key: #headerName, value: #values],
        statusCode: $statusCode
    ]
};

HttpResponse->withBody(^body: HttpMessageBody => HttpResponse) :: [
    protocolVersion: $protocolVersion,
    body: body,
    headers: $headers,
    statusCode: $statusCode
];
