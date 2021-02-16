<?php
declare(strict_types = 1);

namespace Apex\Cluster\Router;

use Apex\Cluster\Exceptions\{ClusterZeroRoutesException, ClusterInvalidArgumentException, ClusterYamlConfigException, ClusterInvalidRoutingKeyException, ClusterPhpClassNotExistsException};


/**
 * Validate class for various configuration, namely the YAML configuration file.
 */
class Validator
{

    /**
     * Validate Yaml file
     */
    public static function validateYamlFile(array $yaml):void
    {

        // Make sure we have routes
        $yaml_routes = $yaml['routes'] ?? [];
        if (count($yaml_routes) == 0) { 
            throw new ClusterZeroRoutesException("No routes exist within the YAML file.  Nothing to do.");
        }

        // Go through routes
        foreach ($yaml_routes as $name => $vars) { 
            self::validateYamlRoute($name, $vars);
        }

    }

    /**
     * Validate YAML route
     */
    public static function validateYamlRoute(string $name, mixed $vars):void
    {

        // Ensure vars is an array
        if (!is_array($vars)) { 
            throw new ClusterYamlConfigException("The route '$name' within the YAML file is not an array.");
        }

        // Check required variables
        foreach (['type', 'routing_keys'] as $req) { 
            if (!isset($vars[$req])) { 
                throw new ClusterYamlConfigException("The route '$name' within Yaml config does not have a '$req' variable associated with it.");
            }
        }

        // Validate msg type
        self::validateMsgType($vars['type']);

        // Ensure routing keys exist
        if (!is_array($vars['routing_keys'])) { 
            throw new ClusterYamlConfigException("Route '$name' does not contain an array of routing keys.");
        }

        // GO through routing keys
        foreach ($vars['routing_keys'] as $routing_key => $php_classes) {

            // Check routing key
            self::validateRoutingKey($routing_key);

            // Check for string
            if (is_string($php_classes)) { 
                $php_classes = ['default' => $php_classes];
            }

            // Go through php classes
            foreach ($php_classes as $alias => $class_name) {
                self::validatePhpClassName($class_name);
            }
        }

    }

    /**
     * Validate routing key
     */
    public static function validateRoutingKey(string $routing_key, bool $use_strict = false):void   
    {

        // Check parts
        $parts = explode('.', $routing_key);
        foreach ($parts as $part) { 
            if (preg_match("/[\W\s]!\*/", $part)) { 
                throw new ClusterInvalidRoutingKeyException("Routing key segments can not contain spaces or special characters, $routing_key");
            }
        }

        // Check number of parts
        if (count($parts) > 3) { 
            throw new ClusterInvalidRoutingKeyException("Routing keys can only have maximum of three segments, $routing_key");
        } elseif ($use_strict === true && count($parts) != 3) { 
            throw new ClusterInvalidRoutingKeyException("Routing keys must have exactly three segments, $routing_key");
        }

    }

    /**
     * Validate php class name
     */
    public static function validatePhpClassName(string $class_name):void
    {

        // Check for merge fields
        if (preg_match("/~(.+)~/", $class_name)) { 
            return;
        }

        // Check
        if (class_exists($class_name) || file_exists($class_name)) { 
            return;
        }
        throw new ClusterPhpClassNotExistsException("The php class / file does not exist, $class_name");

    }

    /**
     * Validate msg type
     */
    public static function validateMsgType(string $msg_type):void
    {
        if (!in_array($msg_type, ['rpc', 'queue', 'broadcast'])) { 
            throw new ClusterInvalidArgumentException("Invalid msg_type '$msg_type'.  Supported message types are: rpc, ack_only, queue, broadcast");
        }
    }

}


