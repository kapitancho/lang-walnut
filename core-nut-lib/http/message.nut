module $http/message:

HttpProtocolVersion := (http_1_0, http_1_1, http_2, http_3);
HttpRequestMethod := (connect, delete, get, head, options, patch, post, put, trace);
HttpRequestTarget = String;
HttpResponseStatusCode = Integer<[101..103], [200..208], 226, [300..308], [400..418], 421, [422..426], 428, 429, 431, 451, [500..511]>;
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
