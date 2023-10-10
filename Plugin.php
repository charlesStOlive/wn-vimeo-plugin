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

    /**
     * Boot method, called right before the request route.
     */
    public function boot(): void
    {

    }

    /**
     * Registers any frontend components implemented in this plugin.
     */
    public function registerComponents(): array
    {
        return []; // Remove this line to activate

        return [
            'Waka\Vimeo\Components\MyComponent' => 'myComponent',
        ];
    }

    /**
     * Registers any backend permissions used by this plugin.
     */
    public function registerPermissions(): array
    {
        return []; // Remove this line to activate
    }

    public function registerFormWidgets(): array
    {
        return [
            'Waka\Vimeo\FormWidgets\VimeoUploader' => 'vimeouploader',
        ];
    }

    /**
     * Registers backend navigation items for this plugin.
     */
    public function registerNavigation(): array
    {
        return []; // Remove this line to activate

        return [
            'vimeo' => [
                'label'       => 'waka.vimeo::lang.plugin.name',
                'url'         => Backend::url('waka/vimeo/mycontroller'),
                'icon'        => 'icon-leaf',
                'permissions' => ['waka.vimeo.*'],
                'order'       => 500,
            ],
        ];
    }
}
