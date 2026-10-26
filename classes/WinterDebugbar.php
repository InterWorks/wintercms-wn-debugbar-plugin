<?php namespace Winter\Debugbar\Classes;

use Closure;
use DebugBar\JavascriptRenderer;
use Fruitcake\LaravelDebugbar\LaravelDebugbar;

class WinterDebugbar extends LaravelDebugbar
{
    /**
     * Returns the JavascriptRenderer for this instance with Winter's styling applied.
     *
     * Laravel Debugbar 4 performs the renderer configuration inside getJavascriptRenderer(),
     * so we defer to the parent to build and cache the renderer and then register Winter's
     * stylesheet via the renderer's asset API (the renderer is cached, so the CSS is only
     * added the first time it is built).
     */
    public function getJavascriptRenderer(?string $baseUrl = null, ?string $basePath = null): JavascriptRenderer
    {
        $alreadyBuilt = $this->jsRenderer !== null;

        $renderer = parent::getJavascriptRenderer($baseUrl, $basePath);

        if (!$alreadyBuilt) {
            $renderer->addAssets(cssFiles: ['debugbar.css'], basePath: __DIR__ . '/../assets/css');
        }

        return $renderer;
    }

    /**
     * Adds a measure, but only once the Debugbar has booted with its time collector.
     *
     * Laravel Debugbar 4 records measures in a time collector it creates before booting, so without this
     * check a Debugbar that never boots (in the console or a queue worker, for example) keeps every
     * measure until the process exits.
     */
    public function addMeasure(
        string $label,
        float $start,
        ?float $end = null,
        array $params = [],
        ?string $collector = null,
        ?string $group = null
    ): void {
        if ($this->hasCollector('time')) {
            parent::addMeasure($label, $start, $end, $params, $collector, $group);
        }
    }

    /**
     * Measures a closure when the time collector is active, and otherwise only runs it.
     */
    public function measure(string $label, Closure $closure, ?string $collector = null, ?string $group = null): mixed
    {
        if (!$this->hasCollector('time')) {
            return $closure();
        }

        return parent::measure($label, $closure, $collector, $group);
    }

    /**
     * Starts a measure when the time collector is active.
     */
    public function startMeasure(string $name, ?string $label = null, ?string $collector = null, ?string $group = null): void
    {
        if ($this->hasCollector('time')) {
            parent::startMeasure($name, $label, $collector, $group);
        }
    }

    /**
     * Stops a measure when the time collector is active.
     */
    public function stopMeasure(string $name): void
    {
        if ($this->hasCollector('time')) {
            parent::stopMeasure($name);
        }
    }
}
