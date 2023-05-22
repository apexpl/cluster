<?php
declare(strict_types = 1);

namespace Apex\Cluster\Router\Loaders;

use Apex\Cluster\Cluster;
use Apex\Cluster\Router\{Router, Validator};
use Apex\Container\Di;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;


/**
 * Handles the loading and saving of router configurations 
 * to and from YAML files.
 */
class YamlLoader extends Router
{


    /**
     * Load from Yaml file.
     */
    public function loadFile(string $file = ''):void
    {

        // Set default filename, if needed
        if ($file == '') { 
            $file = __DIR__ . '/../../../config/router.yaml';
        }

        // Ensure file exists
        if (!file_exists($file)) { 
            throw new \Exception("YAML router configuration file does not exist at $file");
        }

        // Parse Yaml
        $this->load(file_get_contents($file));
    }

    /**
     * Load Yaml from string
     */
    public function load(string $yaml_code):void
    {

        // Initialize
        $cluster = Di::get(Cluster::class);

        // Load YAML code
        try {
            $yaml = Yaml::parse($yaml_code);
        } catch (ParseException $e) { 
            throw new ParseException("Unable to parse YAML code for router.  Message: " . $e->getMessage());
        }

        // Check for ignore line
        if (isset($yaml['ignore']) && $yaml['ignore'] === true) { 
            return;
        }

        // Validate yaml file
        Validator::validateYamlFile($yaml);

        // GO through all instances, if needed
        $instances = $yaml['instances'] ?? [];
        foreach ($instances as $name => $props) { 
            $this->addInstance($name, $props);
        }

        // Go through all routes
        foreach ($yaml['routes'] as $name => $vars) {

            // Get instances
            $instances = $vars['instances'] ?? [];
            if (is_string($instances)) { 
                $instances = [$instances];
            }
            if (count($instances) == 0) { 
                $instances[] = 'all';
            }

            // Check for parameters
            $params = isset($vars['params']) && is_array($vars['params']) ? $vars['params'] : [];

            // Go through routing keys
            foreach ($vars['routing_keys'] as $routing_key => $php_classes) { 

                // Check for string
                if (is_string($php_classes)) { 
                    $php_classes = ['default' => $php_classes];
                }

                // Go through php classes
                foreach ($php_classes as $alias => $class_name) { 
                    $cluster->addRoute($routing_key, $class_name, $alias, $vars['type'], $name, $params, $instances, true);
                }
            }
        }

    }

}






