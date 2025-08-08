<?php

namespace WTE\App\Handlers;

use WTE\Original\Collections\Collection;
use WTE\Original\Events\Handler\EventHandler;

Class OptionsPageHandler extends EventHandler
{
    protected $numberOfArguments = 1;
    protected $priority = 10;    

    public function execute()
    {
        //create new top-level menu
        add_submenu_page(
            'options-general.php',
            'Configuración | Tiempo Estimado de Entrega', 
            'Tiempo Estimado de Entrega', 
            'upload_plugins', 
            __FILE__, 
            [$this, 'renderSettings']
        );

        //call register settings function
        add_action( 'admin_init', function() {
            //register our settings
            foreach ($this->getSettingsData() as $setting) {
                if ($setting->get('group') instanceof Collection) {
                    $setting->get('group')->forEvery(function(Collection $setting) {
                        $this->registerSetting($setting);
                    });
                } else {
                    $this->registerSetting($setting);
                }
            }
        } );
    }

    protected function registerSetting(Collection $setting)
    {
        register_setting(
            'wte-settings', 
            $setting->get('fieldName'),
            $setting->get('settings') instanceof Collection? $setting->get('settings')->asArray() : []
        );
    }
    

    protected function getSettingsData()
    {
        return $this->cache->getIfExists('settingsData')->otherwise(function(){
            return [
                new Collection([
                    'title' => 'Progreso Estados',
                    'fieldName' => 'wte_progress_items',
                    'default' => '{"items": []}'
                ]),
            ];  
        }); 
    }

    function renderSettings() {
        ?>
        <div class="wrap wte-settings">
            <form method="post" action="options.php">
                <?php settings_fields( 'wte-settings' ); ?>
                <?php do_settings_sections( 'wte-settings' ); ?>
                <h1>Configuración | Tiempo Estimado de Entrega</h1>
                <h3>Progreso | Estados</h3>
                <div class="wte-progress-items">
                    
                </div>
                <button class="button wte-add-progress">Añadir estado</button>
                <input type="hidden" name="wte_progress_items" class="wte_progress_data" value='<?php print get_option('wte_progress_items', '{"items": []}'); ?>'>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php 
    } 

    protected function printInputMarkupForField($setting)
    {
        (string) $type = 'text';

        if ($setting->get('settings') instanceof Collection) {
            switch ($setting->get('settings')->get('type')) {
                case 'string':
                    $type = 'text';
                    break;
                case 'boolean':
                    $type = 'checkbox';
                    break;
                case 'integer':
                    $type = 'number';
                    break;
                default:
                    break;
            }
        }
        $optionValue = get_option($setting->get('fieldName'), $setting->get('default'));
        ?>
            <?php if ($setting->hasKey('name')): ?>
                <label for="<?php print $setting->get('fieldName'); ?>"><?php print $setting->get('name'); ?></label>
            <?php endif; ?>
            <input 
                id="<?php print $setting->get('fieldName'); ?>" 
                name="<?php print $setting->get('fieldName'); ?>" 
                type="<?php print $type ?>" 
                <?php if ($type === 'checkbox') :?>
                    <?php if ($optionValue == 'on'):
                        print 'checked="checked"';
                    endif; ?>
                <?php else: ?>
                    value="<?php echo $optionValue? esc_attr($optionValue) : $setting->get('default'); ?>" 
                <?php endif; ?>
                <?php if ($setting->get('width')):  ?>
                    style="width: <?php print $setting->get('width') ?>"
                <?php endif; ?>
            />
            <?php if ($setting->get('rightName')) print "<span>{$setting->get('rightName')}</span>" ?>
        <?php
    }
}