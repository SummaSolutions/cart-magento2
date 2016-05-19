<?php
namespace MercadoPago\MercadoEnvios\Helper;

use Magento\Framework\View\LayoutFactory;


/**
 * Class Data
 *
 * @package MercadoPago\Core\Helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data
    extends \Magento\Payment\Helper\Data
{

    /**
     *path to access token config
     */
    const XML_PATH_ACCESS_TOKEN = 'payment/mercadopago_custom/access_token';
    /**
     *path to public config
     */
    const XML_PATH_PUBLIC_KEY = 'payment/mercadopago_custom/public_key';
    /**
     *path to client id config
     */
    const XML_PATH_CLIENT_ID = 'payment/mercadopago_standard/client_id';
    /**
     *path to client secret config
     */
    const XML_PATH_CLIENT_SECRET = 'payment/mercadopago_standard/client_secret';

    /**
     *api platform openplatform
     */
    const PLATFORM_OPENPLATFORM = 'openplatform';
    /**
     *api platform stdplatform
     */
    const PLATFORM_STD = 'std';
    /**
     *type
     */
    const TYPE = 'magento';

    /**
     * @var \MercadoPago\Core\Helper\Message\MessageInterface
     */
    protected $_messageInterface;

    /**
     * MercadoPago Logging instance
     *
     * @var \MercadoPago\Core\Logger\Logger
     */
    protected $_mpLogger;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Status\Collection
     */
    protected $_statusFactory;

    /**
     * @var \Magento\Framework\Setup\ModuleContextInterface
     */
    protected $_moduleContext;

    /**
     * @var bool flag indicates when status was updated by notifications.
     */
    protected $_statusUpdatedFlag = false;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    public static $enabled_methods = ['mla', 'mlb', 'mlm'];

    /**
     * Data constructor.
     *
     * @param Message\MessageInterface                             $messageInterface
     * @param \Magento\Framework\App\Helper\Context                $context
     * @param LayoutFactory                                        $layoutFactory
     * @param \Magento\Payment\Model\Method\Factory                $paymentMethodFactory
     * @param \Magento\Store\Model\App\Emulation                   $appEmulation
     * @param \Magento\Payment\Model\Config                        $paymentConfig
     * @param \Magento\Framework\App\Config\Initial                $initialConfig
     * @param \Magento\Framework\Setup\ModuleContextInterface      $moduleContext
     * @param \MercadoPago\Core\Logger\Logger                      $logger
     * @param \Magento\Sales\Model\ResourceModel\Status\Collection $statusFactory
     */
    public function __construct(
        \MercadoPago\Core\Helper\Message\MessageInterface $messageInterface,
        \Magento\Framework\App\Helper\Context $context,
        LayoutFactory $layoutFactory,
        \Magento\Payment\Model\Method\Factory $paymentMethodFactory,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Framework\App\Config\Initial $initialConfig,
        \Magento\Framework\Setup\ModuleContextInterface $moduleContext,
        \MercadoPago\Core\Logger\Logger $logger,
        \Magento\Sales\Model\ResourceModel\Status\Collection $statusFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory
    )
    {
        parent::__construct($context, $layoutFactory, $paymentMethodFactory, $appEmulation, $paymentConfig, $initialConfig);
        $this->_messageInterface = $messageInterface;
        $this->_mpLogger = $logger;
        $this->_moduleContext = $moduleContext;
        $this->_statusFactory = $statusFactory;
        $this->_orderFactory = $orderFactory;
    }

    public function isCountryEnabled()
    {
        return (in_array($this->scopeConfig->getValue('payment/mercadopago/country'), self::$enabled_methods));
    }
}
