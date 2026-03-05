module $db/connection:

DatabaseConnection := [dsn: String];
DatabaseConnector := $[connection: DatabaseConnection];

==> DatabaseConnector %% DatabaseConnection :: DatabaseConnector[connection: %];
