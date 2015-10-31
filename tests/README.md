# DirectAdmin API Client Unit Testing

**IMPORTANT**: as the unit tests create and delete multiple account multiple times per run it is *highly*
recommended to disable the `use_uid_counting` feature on your testing server. See more details
[here](http://www.directadmin.com/features.php?id=979).

Unit tests are currently to be performed against a live server. The `phpunit.xml.dist` in the main folder
contains commonly used settings. Three variables must be set, either as constants or as environment
variables:

Name                    | Meaning
----------------------- | -----------------------------------------------------
`DIRECTADMIN_URL`       | Base URL of the DirectAdmin server
`MASTER_ADMIN_USERNAME` | Name of an admin account with sufficient permissions
`MASTER_ADMIN_PASSWORD` | Password of the admin account

Additionally the tests create 3 accounts multiple times, at admin, reseller and user level respectively.
The default names for these accounts are `testadmin`, `testresell` and `testuser`, with generated passwords
sufficiently random not to be hacked during testing. If these values conflict with your testing server you
can override all of them in config, using constants or environment variables.

For all values you can override check the top of the `phpunit-bootstrap.php` file.
