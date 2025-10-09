module $http/request-handler/not-found %% $http/request-handler:

HttpNotFoundHandler := ();
HttpNotFoundHandler ==> HttpRequestHandler %% ~HttpResponseBuilder :: {
    ^request: {HttpRequest} => {HttpResponse} :: httpResponseBuilder(404)
};
