module server %% $http/bundle/autowire-router, $http/autowire/response-helper:

==> HttpRequestHandler %% b: HttpResponseBuilder ::
    ^Any => HttpResponse :: b(200)->withBody('<h1>Hello world!</h1>');
