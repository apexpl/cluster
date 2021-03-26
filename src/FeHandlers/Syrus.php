<?php
declare(strict_types = 1);

namespace Apex\Cluster\FeHandlers;

use Apex\Cluster\FeHandlers\Generic;
use Apex\Cluster\Interfaces\FeHandlerInterface;

/**
 * Front-end handler for the Syrus template engine.
 */
class Syrus extends Generic implements FeHandlerInterface
{

    // Properties
    private array $vars = [];
    private array $blocks = [];
    private array $callouts = [];

    /**
     * Assign template variable.
     */
    public function assign(string $key, mixed $value):void
    {
        $this->vars[$key] = $value;
    }

    /**
     * Add block
     */
    public function addBlock(string $name, array $values):void
    {

        if (!isset($this->blocked[$name])) { 
            $this->blocked[$name] = [];
        }
        $this->blocks[$name][] = $values;

    }

    /**
     * Add callout
     */
    public function addCallout(string $message, string $type = 'success'):void
    {
        $this->callouts[] = [$message, $type];
    }

    /**
     * Set template file
     */
    public function setTemplateFile(string $file, bool $is_locked = false)
    {
        $this->addAction('set_template_file', [$file, $is_locked]);
    }

    /**
     * Get vars
     */
    public function getVars():array
    {
        return $this->vars;
    }

    /**
     * Get blocks
     */
    public function getBlocks():array
    {
        return $this->blocks;
    }

    /**
     * Get callouts
     */
    public function getCallouts():array
    {
        return $this->callouts;
    }




}

