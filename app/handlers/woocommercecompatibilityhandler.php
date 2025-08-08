<?php

namespace WTE\App\Handlers;

use Carbon\Carbon;
use WTE\App\Components\StatusBar;
use WTE\App\WTE\Shortcodes\EstimatedUnitsShortCode;
use WTE\App\WTE\Time\BusinessTime;
use WTE\App\WTE\Time\TimeCalculator;
use WTE\App\WTE\Time\TimeRange;
use WTE\App\WTE\WpDate\DateFormatter;
use WTE\Original\Collections\Collection;
use WTE\Original\Environment\Env;
use WTE\Original\Events\Handler\EventHandler;
use WC_Order;

Class WooCommerceCompatibilityHandler extends EventHandler
{
    protected $numberOfArguments = 1;
    protected $priority = 10;

    const ETA_FIELD_NAME = 'wte_time_range';
    const ORDER_STATUS_ALLOWED = 'processing';

    public static function getETAData($productId, $orderId = null)
    {
        // if order has an eta saved, get it, otherwise, get the global one

        if (metadata_exists('post', $orderId, Self::ETA_FIELD_NAME)) {
            return get_post_meta(
                $orderId, 
                Self::ETA_FIELD_NAME, 
                true
            );
        }

        return get_post_meta(
            $productId,
            Self::ETA_FIELD_NAME, 
            true
        );   
    }
    

    public static function printFields(array $options = [])
    {
        return function() use ($options) {
            $options = new Collection(array_merge([
                'showFooter' => true
            ], $options));
            global $woocommerce, $post;
            (object) $timeRange = new TimeRange(get_post_meta($post->ID, Self::ETA_FIELD_NAME, true));
            ?>
            <!-- id below must match target registered in above add_my_custom_product_data_tab function -->
            <div id="wte-eta" class="panel woocommerce_options_panel">
                <?php
                print $options->get('title');
                woocommerce_wp_select( array(
                    'id'      => 'wte_range_unit',
                    'label'   => __('Unidad de Tiempo', 'woocommerce' ),
                    'options' =>  [
                        'days' => __('Días', Env::textDomain()),
                        'hours' => __('Horas', Env::textDomain())
                    ],
                    'value'   => $timeRange->getUnit(),
                ) );
                woocommerce_wp_text_input( array( 
                    'id'                => 'wte_range_min', 
                    'type'              => 'number',
                    'label'             => __('Mínimo', Env::textDomain()),
                    'description'       => __('', Env::textDomain()),
                    'desc_tip'        => false,
                    'value' => $timeRange->getRange()->get('min')
                ) );
                woocommerce_wp_text_input( array( 
                    'id'                => 'wte_range_max', 
                    'type'              => 'number',
                    'label'             => __('Máximo', Env::textDomain()),
                    'description'       => __('', Env::textDomain()),
                    'desc_tip'        => false,
                    'value' => $timeRange->getRange()->get('max')
                ) );
                ?>
                <p>El cálculo toma en cuenta las horas y días hábiles.</p>
                <hr />
                <?php if ($options->get('showFooter')): ?>
                    <p>ShortCode que muestra "<?php print $timeRange->getReadable(EstimatedUnitsShortCode::getReadableFormatter()); ?>":</p>   
                    <p><?php print EstimatedUnitsShortCode::getDefinition(['id' => $post->ID]); ?></p>
                <?php endif; ?>
            </div>
            <?php
        };   
    }

    static public function saveFields()
    {
        return function ($postId) {

            (string) $unit = isset($_POST['wte_range_unit'])? $_POST['wte_range_unit'] : @$_REQUEST['wte_range_unit'];
            (integer) $min = isset($_POST['wte_range_min'])? $_POST['wte_range_min'] : @$_REQUEST['wte_range_min'];
            (integer) $max = isset($_POST['wte_range_max'])? $_POST['wte_range_max'] : @$_REQUEST['wte_range_max'];

            if (!empty($unit) && !empty($min) && !empty($max)) {
                update_post_meta(
                    $postId, 
                    Self::ETA_FIELD_NAME, 
                    //TimeRange is a Original\Collections\Mapper\Mappable class that 
                    //performs the validation itself,
                    //so there is no need to validate the fields in this file.
                    (new TimeRange([
                        'unit' => $unit,
                        'range' => [
                            'min' => $min,
                            'max' => $max
                        ]
                    ]))->unMap()
                );
            }

        };   
    }
    

    public function execute()
    {
        add_filter( 'woocommerce_product_data_tabs', function ($product_data_tabs) {
            $product_data_tabs['my-custom-tab'] = array(
                'label' => __( 'Tiempo Estimado de Entrega', Env::textDomain()),
                'target' => 'wte-eta',
            );
            return $product_data_tabs;
        });

    add_action( 'woocommerce_product_data_panels', self::printFields());
    add_action('woocommerce_process_product_meta', self::saveFields());

    add_action('woocommerce_order_status_changed', [$this, 'saveETADataOnNewOrders'], 10, 4);

    add_action( 'woocommerce_order_details_after_order_table', function ($order){
            global $woocommerce;

            (boolean) $wooIs3OrHigher = version_compare( $woocommerce->version, 3, ">=" );

            if ($order->get_status() !== static::ORDER_STATUS_ALLOWED) return;

            if ( $wooIs3OrHigher ) {
                $postId = $this->getPostIdFromOrder($order);
            } else {
                $postId = array_values($order->get_items())[0]['item_meta']['_product_id'][0];
            } 

            $timerange = new TimeRange(static::getETAData($postId, $order->get_id()));

            if (!$timerange->hasValidRange()) return;

        (object) $businessTime = new BusinessTime(
            $hours = new Collection([
                8, 9, 10, 11, 12, 13, 14, 15, 16, 18, 19, 20
            ]),
            $days = new Collection([
                BusinessTime::MONDAY,
                BusinessTime::TUESDAY,
                BusinessTime::WEDNESDAY,
                BusinessTime::THURSDAY,
                BusinessTime::FRIDAY,
            ])
        );

        (object) $timeCalculator = new TimeCalculator(
            $timerange,
            $businessTime
        );

        (object) $orderDate = Carbon::createFromFormat('Y-m-d H:i:s', $order->get_date_modified()->format('Y-m-d H:i:s'));

        (object) $calculatedDate = $timeCalculator->getCalculationFromDate($orderDate);
        (object) $minFormatter = new DateFormatter($calculatedDate->get('min'));
        (object) $maxFormatter = new DateFormatter($calculatedDate->get('max'));
        (object) $shortcode = new EstimatedUnitsShortCode(['id' => $postId, 'order_id' => $order->get_id()], '');
        ?>
        <div class="wte-date">
            <p class="wte-date-estimation">El pedido que usted realizó tarda de <span class="wte-time-values"><?php print $shortcode->render(); ?></span>Esto quiere decir que su pedido será entregado a partir del <b><?php print $minFormatter->readable(); ?></b> al <b><?php print $maxFormatter->readable(); ?></b>.</p> 
            <?php 
                $product = wc_get_product($postId);

                if (is_object($product) && $product->is_virtual()) {
                    (object) $statusBar = new StatusBar($timeCalculator, $calculatedDate, $orderDate);
                             $statusBar->render();
                }
            ?>
            <div class="clearfix"></div>
            <div class="wte-calendar" data-min="<?php print $minFormatter->standardFormat() ?>" data-max="<?php print $maxFormatter->standardFormat() ?>" data-is-next-month="<?php print $maxFormatter->getMonth() != $minFormatter->getMonth() ?>"></div>
            <p>Recuerde que solo cuentan los DIAS / HORAS HABILES, esto quiere decir que los sábados, domingos y días festivos no se trabaja.</p>
            <p>Por lo tanto no son días laborales. Sea paciente y espere un correo nuestro.<br /> Para cualquier duda que tenga, contáctenos, estamos para servirle.</p>
        </div>
    <?php
    }, 10, 1 );
    }

    public function getPostIdFromOrder($order)
    {
        return array_values($order->get_items())[0]->get_product_id();
    }

    public function saveETADataOnNewOrders($orderId, $oldStatus, $newStatus, WC_Order $order)
    {
        if (in_array($newStatus, [static::ORDER_STATUS_ALLOWED])) {
            update_post_meta(
                $postId = $order->get_id(),
                $key = Self::ETA_FIELD_NAME,
                $value = static::getETAData($this->getPostIdFromOrder($order))
            );
        }
    }
}