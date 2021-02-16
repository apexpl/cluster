
# Contributing

Contributions always welcome, and proper credit will always be given to any contributors.  Standard rules apply:

- PSR compliant code.
- Must include any necessary tests.
- One contribution per PR
- Please prefix PRs as necessary (eg. hotfix/some_fix for fixes, feature/cool_feature for new feature, etc.).

## Moving Forward

Things that come to mind that could be developed into Cluster:

* Additional front-end endlers for platforms such as Wordpress, Twig, Smarty, et al.
* Attributes support.  Auto generate YAML configuration file by scanning directory of PHP classes, look for a "listensTo()" attribute or similar, and generates routes based on that.
* Look into asynchronous calls, which I believe is somewhat already there via the Listener::dispatch() method, which declares all exchanges and queues without calling $broker->wait().
* Testing of AWS SQS, and implementation of another BrokerInterface if necessary.






