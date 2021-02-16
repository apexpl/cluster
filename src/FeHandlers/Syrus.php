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
    private array $template_vars = [];
    private array $callouts = [];

    /**
     * Assign template variable.
     */
    public function assign(string $key, mixed $value):void
    {
        $this->template_vars[$key] = $value;
    }

    /**
     * Add callout
     */
    public function addCallout(string $message, string $type = 'success'):void
    {
        $this->callouts[] = [$message, $type];
    }

    /**
     * Set URI
     */
    public function setUri(string $uri, bool $is_locked = false)
    {
        $this->addAction('set_uri', [$uri, $is_locked]);
    }

    /**
     * Set theme
     */
    public function setTheme(string $theme_alias):void
    {
        $this->addAction('set_theme', $theme_alias);
    }

    /**
     * Set area
     */
    public function setArea(string $area):void
    {
        $this->addAction('set_area', $area);
    }

    /**
     * Set http status
     */
    public function setHttpStatus(int $code = 200:void
    {
        $this->addAction('set_http_status', $code);
    }

    /**
     * Set content type
     */
    public function setContentType(string $content_type = 'text/html'):void
    {
        $this->addAction('set_content_type', $content_type);
    }

    /**
     * Set http header
     */
    public function setHttpHeader(string $key, string $value):void
    {
        $this->addAction('set_http_header', [$key, $value]);
    }

    /**
     * Set cookie
     */
    public function setCookie(string $key, string $value):void
    {
        $this->addAction('set_cookie', [$key, $value]);
    }

}


