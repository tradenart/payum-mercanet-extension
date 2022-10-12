<?php
namespace Tradenart\Payum\Mercanet;

use Http\Message\MessageFactory;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\HttpClientInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Payum\Core\Reply\HttpPostRedirect;

class Api
{
    public const PRODUCTION_SERVER = 'https://payment-webinit.mercanet.bnpparibas.net/paymentInit';
    
    public const SANDBOX_SERVER = 'https://payment-webinit-mercanet.test.sips-atos.com/paymentInit';
    
    public const INTERFACE_VERSION = 'HP_2.20';
    
    public const HASH_MAC = 'sha256';
    
    public const amount = 'amount';
    
    public const currencyCode = 'currencyCode';
    
    public const merchantId = 'merchantId';
    
    public const normalReturnUrl = 'normalReturnUrl';
    
    public const transactionReference = 'transactionReference';
    
    public const keyVersion = 'keyVersion';
    
    public const automaticResponseUrl = 'automaticResponseUrl';
    
    
    /**
     * @var HttpClientInterface
     */
    protected $client;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var array
     */
    protected $options = [];
    
    protected $router;

    /**
     * @param array               $options
     * @param HttpClientInterface $client
     * @param MessageFactory      $messageFactory
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if an option is invalid
     */
    public function __construct(array $options, HttpClientInterface $client, MessageFactory $messageFactory, $router)
    {
        $this->options = $options;
        $this->client = $client;
        $this->messageFactory = $messageFactory;
        $this->router = $router->getRouter();
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    public function doPayment(array $details)
    {
        $details[self::merchantId] = $this->options['merchantId'];
        $details[self::keyVersion] = $this->options['key_version'];
                
        $form['Data'] = $this->getData($details);
        $form['InterfaceVersion'] = self::INTERFACE_VERSION;
        $form['Seal'] = $this->computeSeal($this->getData($details));
        
        $authorizeTokenUrl = $this->getAuthorizeTokenUrl();
        
        throw new HttpPostRedirect($authorizeTokenUrl, $form);
    }

    /**
     * @return string
     */
    protected function getApiEndpoint()
    {
        $servers = array();
        if ($this->options['sandbox']) {
            $server = self::SANDBOX_SERVER;
        } else {
            $server = self::PRODUCTION_SERVER;
        }
        
        return $server;
    }
    
    /**
     * @return string
     */
    public function getAuthorizeTokenUrl()
    {
        return $this->getApiEndpoint();
    }
    
    protected function getData($details)
    {
        $array_details = array();
        foreach($details as $key=>$value){
            $array_details[] = $key.'='.$value;
        }
        
        return implode('|', $array_details);
    }
    
    /**
     * @param $hmac string hmac key
     * @param $fields array fields
     * @return string
     */
    protected function computeSeal($details)
    {
        return hash(self::HASH_MAC, $details.$this->options['secret_key']);
    }
    
    public function getResults($content){
        $content = rawurldecode($content);
        
        $array_content = array();
        
        foreach(explode('|', $content) as $key=>$value){
            $data = explode('=', $value);
            
            if(isset($data[1])){
                $array_content[$data[0]] = $data[1];
            }
        }
        
        return $array_content;
    }
}
