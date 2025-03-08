module $http/route-tpl %% $http/route, $tpl:

ViewResponseBody = $[statusCode: HttpStatusCode, contentType: String];
ViewResponseBody ==> HttpResponseBodyFromParameter ::
    ^view: Any => Result<HttpResponse, Any> %% [~TemplateRenderer] :: {
        [
             statusCode: $statusCode,
             protocolVersion: HttpProtocolVersion.HTTP11,
             headers: [:]->withKeyValue[key: 'Content-Type', value: [$contentType]],
             body: %templateRenderer => render(view)
        ]
    };

HttpRouteBuilder->httpGetAsView(^[pattern: RoutePattern, handler: Type<^Nothing => Any>] => HttpRoute) :: HttpRoute[
    method: HttpRequestMethod.GET,
    pattern: #pattern,
    requestBody: EmptyRequestBody()->asHttpRequestBodyToParameter,
    handler: #handler,
    response: {ViewResponseBody[200, 'text/html']}->asHttpResponseBodyFromParameter
];