<?php
namespace Tradenart\Payum\Mercanet\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Tradenart\Payum\Mercanet\Api;
use Payum\Core\Request\GetCurrency;
use Payum\Core\Action\GatewayAwareAction;

class ConvertPaymentAction extends GatewayAwareAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param Convert $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();        
        $details = ArrayObject::ensureArrayObject($payment->getDetails());
        
        $this->gateway->execute($currency = new GetCurrency($payment->getCurrencyCode()));
        
        $details[Api::currencyCode] = $currency->numeric;
        $details[Api::transactionReference] = 'skdjnvkjhUIKjh'.$payment->getCommande()->getId();
        $details[Api::amount] = $payment->getTotalAmount();
        
        $token = $request->getToken();
        $details[Api::normalReturnUrl] = $token->getAfterUrl();

        $request->setResult((array) $details);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            $request->getTo() == 'array'
        ;
    }
}
