<?php namespace Waka\Vimeo;

use Backend;
use Backend\Models\UserRole;
use System\Classes\PluginBase;
use Lang;
use App;
use Config;
use Illuminate\Foundation\AliasLoader;
/**
 * vimeo Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     */
    public function pluginDetails(): array
    {
        return [
            'name'        => 'waka.vimeo::lang.plugin.name',
            'description' => 'waka.vimeo::lang.plugin.description',
            'author'      => 'waka',
            'icon'        => 'icon-leaf'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     */
    public function register(): void
    {
        $registeredAppPathConfig = require __DIR__ . '/config/vimeo.php';
        Config::set('vimeo', $registeredAppPathConfig);
    }

    

}
