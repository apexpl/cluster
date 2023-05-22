
# Parameter Based Routing

Cluster also supports parameter based routing meaning on top of matching the correct routing key, a message must also meet additional criteria.  For example, if a RPC call is made with every template parsed you may wish a certain route only execute on a specific page with request method of POST, or only when the user is authenticated.


## Defining Routes with Parameters

Parameter conditions are an associative array, the keys being the request key / field to check, and the value being the condition it must meet.  If adding routes within PHP via the `addRoute()` variable you can simply pass the `$params` array as the sixth argument to the method, as described in [Adding Routes in PHP](router_php.md).

Otherwise if defining routes via a YAML router file, you may define parameters within route definitions such as:

~~~
   login_page:
    type: rpc
    params:
      method: "== POST"
      uri: "=~ login$"
      post.submit: "== login_user"
    routing_keys:
      twig.template.parse: App\Templates\Login
~~~

The above route will only accept messages  if the request method is POST, the requested URI ends with "login", and the `$_POST[submit]` variable is equal to "login_user".


## Parameter Definitions

The request keys / fields that may be checked are the same found within the `$request` property of the `MessageRequestInterface` object.  Please see the [Message Requests](message_requests.md) page for details on elements found within this array.  

Any element within that array may be checked against.  If the value of the element is n array itself, this can be designated by a period within the key.  For example, assuming `$request[post]` is a sanitized `$_POST` array, and you wanted to check against the "action" form field, you would use "post.action" as the key.

Parameter definitions are an associative array, the keys being the value to check against as described above, and the values are the conditions to check against.  Each condition must begin with a two character operator, followed by the actual conditional string.  Supported operators are:

Operator | Description
------------- |------------- 
`==` | Equal to
`!=` | Not equal to.
`>=` | Greater than or equal to.
`<=` | Less than or equal to.
`=~` | Matches a regular expression.  Note, any special characters including forward slashes must be escaped, and within YAML must be escaped with three backslashes (ie. \\\).
`!~` | Does not match a regular expression.  Note, any special characters including forward slashes must be escaped, and within YAML must be escaped with three backslashes (ie. \\\).


