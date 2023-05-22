
# Auto-Routing

Cluster allows messages to be auto-routed to different PHP classes based on the named routing key.  Simply define a route to the routing key "all", and within the PHP class use merge fields surrounded by tildas ~, such as:

> `App\~package.title~\~module.title~`

For example, in the YAML router file you would use something such as:

~~~
  rpc.catchall:
    type: rpc
    routing_keys:
      all: App\~package.title~\~module.title~
~~~

With the above example, when a message is sent to the routing key "users.profile.delete" for example, the method at `App\Users\Profile::delete()` would be executed.

## Available Merge Fields

All routing keys are comprised of three segments separated by periods:

> PACKAGE.MODULE.METHOD

All messages also are assigned a message type which can be either, rpc, queue, broadcast.  The following merge fields are available in different naming conventions and can be used within any PHP class when defining routes:

* ~package~
* ~package.title~
* ~package.camel~
* ~package.lower~
* ~module~
* ~module.title~
* ~module.camel~
* ~module.lower~
* ~method~
* ~method.title~
* ~method.camel~
* ~method.lower~
* ~msg_type~
* ~msg_type.title~
* ~msg_type.camel~
* ~msg_type.lower~


