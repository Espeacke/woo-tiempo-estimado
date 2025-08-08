<?php

namespace WTE\App\Handlers;

use Exception;
use WC_Order;
use WTE\App\Handlers\WooCommerceCompatibilityHandler;
use WTE\Original\Events\Handler\EventHandler;

Class OrderDateHandler extends EventHandler
{
    protected $numberOfArguments = 1;
    protected $priority = 10;

    const META_KEY_ORIGINAL_DATE = 'wte-original-order-date';
    const META_KEY_MODIFIED_DATE = 'wte-modified-order-date';

    const DATE_FORMAT = 'Y-m-d H:i:s';

    public function execute()
    {
        $this->registerMetaBox();      
    }

    protected function registerMetaBox()
    {
        // Adding Meta container admin shop_order pages
        add_action( 'add_meta_boxes', function() {
            add_meta_box(
                $id = 'wte_reset_date', 
                $title = __('Reinicio de Fecha de Pedido','woocommerce'), 
                $renderer = [$this, 'renderMetaBox'], 
                $screen = 'shop_order', 
                $context = 'side', 
                $priority = 'core' 
            );

            add_meta_box(
                $id = 'wte_reset_eta_data', 
                $title = __('Parametros del calculo del tiempo estimado','woocommerce'), 
                $renderer = [$this, 'renderETAMetaBox'], 
                $screen = 'shop_order', 
                $context = 'side', 
                $priority = 'core' 
            );
        });

        $this->registerAjax();
    }

    protected function registerAjax()
    {
        add_action('wp_ajax_wte_handle_order_date', [$this, 'handleOrderAction']);
        add_action('wp_ajax_wte_handle_remove_eta_data', [$this, 'handleRemoveEtaData']);
    }

    public function handleOrderAction()
    {
        (object) $user = wp_get_current_user();

        try {
            if (in_array('administrator', $user->roles)) {
                if (isset($_POST['wte-order-id']) && is_numeric($_POST['wte-order-id']) && isset($_POST['wte-order-action']) && in_array($_POST['wte-order-action'], ['restart', 'restore'])) {
                    (object) $order = wc_get_order($_POST['wte-order-id']);
                    (string) $action = $_POST['wte-order-action'];
                    if (!($order instanceof \WC_Order)) {
                        throw new Exception('id the orden invalida');
                    }
                    $this->updateOrder($order, $action);
                    exit('OK');
                } else {
                    throw new Exception("parametros invalidos");
                }
            }
        } catch (Exception $exception) {
            http_response_code(400);
            exit($exception->getMessage());
        }

        exit();
    }
    
    protected function updateOrder(WC_Order $order, $action)
    {
        switch ($action) {
            case 'restart':
                $this->restartOrderDate($order);
                break;
            case 'restore':
                $this->restoreOrderDate($order);
                break;  
        }
    }

    protected function restartOrderDate(WC_Order $order)
    {
        (string) $currentDate = '';

        if (!$this->hasOriginalDateInLog($order)) {
            update_post_meta(
                $order->get_id(), 
                static::META_KEY_ORIGINAL_DATE, 
                $order->get_date_created()->format(static::DATE_FORMAT)
            );
        }

        (string) $currentDate = current_time(static::DATE_FORMAT);

        $order->set_date_created($currentDate);
        $order->set_date_paid($currentDate);
        $order->set_date_modified($currentDate);

        $order->save();

        add_post_meta(
            $order->get_id(), 
            static::META_KEY_MODIFIED_DATE, 
            $currentDate,
            $unique = false
        );
    }

    protected function restoreOrderDate(WC_Order $order)
    {
        (string) $currentDate = '';

        if ($this->hasOriginalDateInLog($order)) {
            (string) $originalDate = $this->getOriginalDateFromLog($order);

            $order->set_date_created($originalDate);
            $order->set_date_paid($originalDate);
            $order->set_date_modified($originalDate);

            $order->save();

            delete_post_meta(
                $order->get_id(), 
                static::META_KEY_MODIFIED_DATE
            );
        }
    }

    protected function hasOriginalDateInLog(WC_Order $order)
    {
        return !empty($this->getOriginalDateFromLog($order));
    }
    
    protected function getOriginalDateFromLog(WC_Order $order)
    {
        return $order->get_meta(static::META_KEY_ORIGINAL_DATE);
    }

    protected function getModifiedDatesFromLog(WC_Order $order)
    {
        return $order->get_meta(static::META_KEY_MODIFIED_DATE, $single = false);   
    }
    

    public function renderMetaBox()
    {
        (string) $restartText = 'Reiniciar Fecha';
        (string) $restoreText = 'Restaurar Original';

        /*mixed*/ $order = wc_get_order(isset($GLOBALS['post'])? $GLOBALS['post'] : null);

        ?>
        <div class="wte-reset-buttons">
            <button 
                id="woo-restart-order-date" 
                class="button button-primary" 
                data-text-idle="<?php print $restartText; ?>"
                data-text-active="Reiniciado..."
            >
                <?php print $restartText; ?>
            </button>
            <?php if (($order instanceof WC_Order) && $this->hasOriginalDateInLog($order)): ?>
                <button 
                    id="woo-restore-order-date" 
                    class="button" 
                    data-text-idle="<?php print $restoreText; ?>"
                    data-text-active="Restaurando..."
                >
                    <?php print $restoreText; ?>
                </button>
            <?php endif; ?>
        </div>

        <?php if (($order instanceof WC_Order) && $this->hasOriginalDateInLog($order)): ?>
            <ul class="wte-log">
                <li id="wte-log-original">Fecha original: <?php print $this->getDateFormatted($this->getOriginalDateFromLog($order)) ?></li>
                <?php foreach ($this->getModifiedDatesFromLog($order) as $date): ?>
                    <li>Actualizado: <?php print $this->getDateFormatted($date->get_data()['value']) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <?php
    }

    protected function orderHasCustomEtaData(WC_Order $order)
    {
        return $order->get_meta(WooCommerceCompatibilityHandler::ETA_FIELD_NAME);
    }

    public function renderETAMetaBox()
    {
        /*mixed*/ $order = wc_get_order(isset($GLOBALS['post'])? $GLOBALS['post'] : null);

        if (($order instanceof WC_Order) && $this->orderHasCustomEtaData($order)) {
            ?>
                <div>
                    <button 
                        id="wte-remove-custom-eta-data" 
                        class="button" 
                        data-text-active="Actualizando..."
                        data-text-idle="Actualizar Horas/Dias Habiles"
                    >
                        Actualizar Horas/Dias Habiles
                    </button>
                </div>
            <?php
        } else {
            print '<p>Esta orden esta usando la mas reciente versión de días/horas habiles para el calculo del tiempo estimado de entrega.</p>';
        }

        ?>
        <?php
    }

    public function handleRemoveEtaData()
    {
        (object) $user = wp_get_current_user();

        try {
            if (in_array('administrator', $user->roles)) {
                if (isset($_POST['wte-order-id']) && is_numeric($_POST['wte-order-id'])) {
                    (object) $order = wc_get_order($_POST['wte-order-id']);

                    if (!($order instanceof \WC_Order)) {
                        throw new Exception('id the orden invalida');
                    }
                    $this->removeETAData($order);
                    exit('OK');
                } else {
                    throw new Exception("parametros invalidos");
                }
            }
        } catch (Exception $exception) {
            http_response_code(400);
            exit($exception->getMessage());
        }

        exit();
    }

    protected function removeETAData($order)
    {
        delete_post_meta(
            $order->get_id(), 
            WooCommerceCompatibilityHandler::ETA_FIELD_NAME
        );
    }

    protected function getDateFormatted($dateString)
    {
        return date_i18n(get_option( 'date_format' ), strtotime($dateString));   
    }
    
}