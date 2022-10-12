<?php
namespace Tradenart\Payum\Mercanet\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Exception\RequestNotSupportedException;
use Tradenart\Payum\Mercanet\Action\Api\BaseApiAwareAction;
use Tradenart\Payum\Mercanet\Api;
use Payum\Core\Security\TokenInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Core\Request\GetHttpRequest;

class CaptureAction extends BaseApiAwareAction implements ActionInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface
{
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);
        
        $details = ArrayObject::ensureArrayObject($request->getModel());
        
        $httpRequest = new GetHttpRequest();
        $this->gateway->execute($httpRequest);
        
        $array_content = $this->api->getResults($httpRequest->content);
        
        if (isset($array_content['responseCode'])) {
            return;
        }
        
        if (null === $details[Api::automaticResponseUrl] && $request->getToken() instanceof TokenInterface) {
            $notifyToken = $this->tokenFactory->createNotifyToken(
                $request->getToken()->getGatewayName(),
                $request->getToken()->getDetails()
            );
            
            $details[Api::automaticResponseUrl] = $notifyToken->getTargetUrl();
        }
        
        $this->api->doPayment((array) $details);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
