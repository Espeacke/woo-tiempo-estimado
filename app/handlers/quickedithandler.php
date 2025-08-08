<?php

namespace WTE\App\Handlers;

use WTE\App\WTE\Time\TimeRange;
use WTE\Original\Events\Handler\EventHandler;

Class QuickEditHandler extends EventHandler
{
    protected $numberOfArguments = 1;
    protected $priority = 10;

    public function execute()
    {
        add_action('quick_edit_custom_box',               $this->renderCustomBox(true));
        add_action('woocommerce_product_bulk_edit_start', $this->renderCustomBox(false));
        
        add_action('manage_product_posts_custom_column', [$this, 'addDataToRow'], 10, 2);       


        add_action('save_post',                          WooCommerceCompatibilityHandler::saveFields());
        add_action('woocommerce_product_bulk_edit_save', function($product) {
            $product_id = method_exists( $product, 'get_id' ) ? $product->get_id() : $product->id;

            call_user_func(WooCommerceCompatibilityHandler::saveFields(), $product_id);
        });
    }

    public function renderCustomBox($doChecks = false)
    {
        return function($column = '', $postType = '') use ($doChecks) {
            if ($doChecks && $column !== 'sku') return;
            ?>

            <fieldset class="inline-edit-col-right">
                <?php call_user_func(WooCommerceCompatibilityHandler::printFields([
                    'title' => '<h4>Tiempo Estimado</h4>',
                    'showFooter' => false
                ])) ?>
            </fieldset>
            <?php
        };
    }
    

    public function addDataToRow($col, $id){
        if ($col === 'name') {
            (object) $timeRange = new TimeRange(get_post_meta($id, WooCommerceCompatibilityHandler::ETA_FIELD_NAME, true));

            ?>
                <div id="wte-data-<?php print $id; ?>" class="hidden wte-data">
                    <div data-name="wte_range_unit" data-value="<?php print $timeRange->getUnit();  ?>"></div>
                    <div data-name="wte_range_min" data-value="<?php print $timeRange->getRange()->get('min');  ?>"></div>
                    <div data-name="wte_range_max" data-value="<?php print $timeRange->getRange()->get('max');  ?>"></div>
                </div>
            <?php
        }
    }
    
}