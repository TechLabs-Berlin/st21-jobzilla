
# CloudTables API client

This library can be used by PHP applications to interface with the CloudTables API. At the moment the only facility provided it to request access security tokens. These tokens can then be used to securely request a CloudTable interface for a specific table, with the access rights defined by the security key and requests from this library.


## Use:

### Direct import

Import the library from the path you have it saved in:

```php
require 'Api.php';
```

### Composer

Install the library using `composer require cloudtables/client`. Then in your PHP load the composer autoloader as normal - when the CloudTables\Api class is referenced in your code, it will now be automatically included:

```php
require 'vendor/autoload.php';
```


### Getting a token

Get a security token:

```php
$api = new CloudTables\Api('subdomain', 'apiKey', [
	'userId' => 'yourUniqueUserId',
	'userName' => 'User name / label'
]);

$token = $api->accessToken();
```

where:

* `subdomain` would be replaced by the sub-domain for your CloudTables application.
* `apiKey` would be replaced by your API Key (see the _Security / API Keys_ section in your CloudTables application)
* `userId` is optional, but will be used to uniquely identify user's in the CloudTables interface.
* `userName` is also optional, but can be used to help identify who made what changes when reviewing logs in CloudTables. It is recommended you include `userId` and `userName`.


## Using the token

Once you have a security access token, you will want to use that as part of the script to request the CloudTables embedded table / form on your page - exactly how you get the token onto your page will depend upon what templating engine or other HTML generation you are using, but it could be as simple as echo-ing out the token:

```html
<script src="..." data-token="<?=$token?>"></script>
```

See the _Data set / Embed_ section of your CloudTables application for more information on how to embed a CloudTables view.


## Storing the token

There is a small overhead to requesting a security access token, since it must make a request to the CloudTables' servers to validate the key and create the token. While this is unavoidable for the first request by a user, you might wish to store the access token in a session object, allowing the token to only be generated when the session is first set-up.

Example:

```php
if (! isset($_SESSION['cloudtables-token'])) {
	$api = new CloudTablesApi(...);
	$_SESSION['cloudtables-token'] = $api->token();
}

$token = $_SESSION['cloudtables-token'];
```
