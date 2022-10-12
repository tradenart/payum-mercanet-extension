<?php
namespace Tradenart\Payum\Mercanet\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHttpRequest;
use Tradenart\Payum\Mercanet\Action\Api\BaseApiAwareAction;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;

class StatusAction extends BaseApiAwareAction implements ActionInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface
{
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;
    
    const SUCCESS = "00";
    
    /**
     * {@inheritDoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);
        
        $model = ArrayObject::ensureArrayObject($request->getModel());
        
        $httpRequest = new GetHttpRequest();
        $this->gateway->execute($httpRequest);
        
        $array_content = $this->api->getResults($httpRequest->content);
                
        if (!isset($array_content['responseCode']) || null === $array_content['responseCode']) {
            $request->markNew();
            return;
        }
        
        if (isset($array_content['responseCode']) && self::SUCCESS === $array_content['responseCode']) {
            $request->markCaptured();
            return;
        }
        
        $request->markFailed();
        
    }
    
    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
        $request instanceof GetStatusInterface &&
        $request->getModel() instanceof \ArrayAccess
        ;
    }
}
