<?php

namespace Sportotal\DataImport\Cron;

use Sportotal\DataImport\Model\Import\Adapter as ImportAdapter;
use \Magento\ImportExport\Model\Import;
use Magento\Framework\App\Filesystem\DirectoryList;

class ImportProductData extends \SummaSolutions\DataImport\Cron\ImportProductData
{
    /**
     * @var \Summa\Brands\Api\BrandRepositoryInterfaceFactory
     */
    protected $brandRepositoryFactory;
    
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * ImportProductData constructor.
     * @param \SummaSolutions\DataImport\Helper\DataImportHelperInterface $helper
     * @param Import $import
     * @param \Magento\Framework\FilesystemFactory $filesystem
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Summa\Brands\Api\BrandRepositoryInterfaceFactory $brandRepositoryFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     */
    public function __construct(
        \SummaSolutions\DataImport\Helper\DataImportHelperInterface     $helper,
        \Magento\ImportExport\Model\Import                              $import,
        \Magento\Framework\FilesystemFactory                            $filesystem,
        \Magento\Framework\App\Config\ScopeConfigInterface              $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder               $transportBuilder,
        \Summa\Brands\Api\BrandRepositoryInterfaceFactory               $brandRepositoryFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory  $productCollectionFactory
        
    ) {
        parent::__construct($helper, $import, $filesystem, $scopeConfig, $transportBuilder);
        $this->brandRepositoryFactory     = $brandRepositoryFactory;
        $this->productCollectionFactory   = $productCollectionFactory;
    }

    public function execute()
    {
       parent::createFolders();

        $files = scandir($this->folder);

        foreach ($files as $file) {
            if (strrpos($file, "csv") === false
                && strrpos($file, "xml") === false
            ) {
                continue;
            }

            if (self::validateData($file) && parent::startImport()) {
                $this->messages[] = __("File ") . $file . __(" successfully imported. Moved to 'done' folder");
                 rename($this->folder . $file, $this->folder . 'done/' . date('Y-m-d H:i:s', time()) . " - " . $file);
            } else {
                $this->messages[] = __("File ") . $file . __(" validation failed. Moved to 'failed' folder");
                rename($this->folder . $file, $this->folder . 'failed/' . date('Y-m-d H:i:s',
                        time()) . " - " . $file); // validation failed, move the file to the 'failed' folder
            }
        }

        if (count($this->messages) > 0) {
            parent::sendEmail();
        }
    }

    /**
     * @param $file
     * @return boolean
     */
    protected function validateData($file)
    {

        $this->data['validation_strategy'] = "validation-skip-errors";
        $this->data['allowed_error_count'] = "10000";

        $this->import->setData($this->data);

        $source = new \Sportotal\DataImport\Model\Import\Source\Csv($this->folder . $file,
            $this->filesystem->create()->getDirectoryWrite(DirectoryList::ROOT),
            $this->brandRepositoryFactory,
            $this->productCollectionFactory,
            $this->data[\Magento\ImportExport\Model\Import::FIELD_FIELD_SEPARATOR]
            );

       return $this->import->validateSource($source);
    }
}