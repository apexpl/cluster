<?php
declare(strict_types = 1);


namespace Apex\Cluster\Router;

use Apex\Cluster\Interfaces\MessageRequestInterface;
use Apex\Cluster\Exceptions\{ClusterParamValueNotExistsException, ClusterInvalidParamOperatorException};


/**
 * Checks parameter based routes against incoming messages.
 */
class ParamChecker
{

    /**
     * Check params
     */
    public static function check(array $params, MessageRequestInterface $msg):bool
    {

        // Initialize
        $ok = true;
        $request = $msg->getRequest();

        // Check params
        foreach ($params as $key => $condition) { 

            // Get value being checked
            if (!$value = self::getValue($key, $request)) { 
                $ok = false;
                break;
            }

            // Parse condition
            if (!preg_match("/^(..)(.*)$/", $condition, $match)) { 
                throw new ClusterInvalidParamOperatorException("Invalid parameter condition, $condition");
            } elseif (!in_array($match[1], ['==', '!=', '=~', '!~', '>=', '<='])) { 
                throw new ClusterInvalidParamOperatorException("Invalid parameter operator, '$match[1]'.  Supported operators are: ==, !=, =~, !~, >=, <=");
            }
            list($opr, $condition) = [$match[1], trim($match[2])];

            // Check value
            if (!self::checkValue($value, $opr, $condition)) { 
                $ok = false;
                break;
            }
        }

        // Return
        return $ok;
    }

    /**
     * Get value
     */
    private static function getValue(string $key, array $request):?string
    {

        // Initialize
        $value = null;

        // Check for array element
        if (preg_match("/^(\w+?)\.(.+?)/", $key, $match) &&  
            isset($request[$match[1]]) && 
            is_array($request[$match[1]]) && 
            isset($request[$match[1]][$match[2]])
        ) { 
            $value = $request[$match[1]][$match[2]];

        // Check singular element
        } elseif (isset($request[$key])) { 
            $value = $request[$key];
        }

        // Return
        return $value;
    }

    /**
     * Check value
     */
    private static function checkValue(string $value, string $opr, string $condition):bool
    {

        // Check value depending on operator
        if ($opr == '==' && $value == $condition) { 
            return true;
        } elseif ($opr == '!=' && $value != $condition) { 
            return true;
        } elseif ($opr == '>=' && (int) $value >= (int) $condition) { 
            return true;
        } elseif ($opr == '<=' && (int) $value <= (int) $condition) { 
            return true;
        } elseif ($opr == '=~' && preg_match("/$condition/", $value)) { 
            return true;
        } elseif ($opr == '!~' && !preg_match("/$condition/", $value)) { 
            return true;
        }

        // Return 
        return false;
    }

}


