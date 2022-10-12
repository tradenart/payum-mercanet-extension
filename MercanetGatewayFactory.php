<?php
namespace Tradenart\Payum\Mercanet;

use Tradenart\Payum\Mercanet\Action\AuthorizeAction;
use Tradenart\Payum\Mercanet\Action\CancelAction;
use Tradenart\Payum\Mercanet\Action\ConvertPaymentAction;
use Tradenart\Payum\Mercanet\Action\CaptureAction;
use Tradenart\Payum\Mercanet\Action\NotifyAction;
use Tradenart\Payum\Mercanet\Action\RefundAction;
use Tradenart\Payum\Mercanet\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class MercanetGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'mercanet',
            'payum.factory_title' => 'mercanet',
            'payum.action.capture' => new CaptureAction(),
            'payum.action.refund' => new RefundAction(),
            'payum.action.cancel' => new CancelAction(),
            'payum.action.notify' => new NotifyAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'merchantId'           => null,
                'secret_key'           => null,
                'key_version'           => null,
                'interface_version'           => Api::INTERFACE_VERSION,
                'sandbox'        => true,
            );
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = [
                'merchantId',
                'secret_key',
                'key_version',
                'interface_version',
            ];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Api((array) $config, $config['payum.http_client'], $config['httplug.message_factory'], new Router());
            };
        }
    }
}
