<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Controller\Adminhtml\Lookbook;
 
use Magento\Framework\Controller\ResultFactory;
 
/**
 * Lookbookpro Adminhtml Image Upload Controller
 */
class ImageUpload extends \Magento\Backend\App\Action
{
    /**
     * Image uploader
     *
     * @var \Magento\Catalog\Model\ImageUploader
     */
    protected $imageUploader;
 
    /**
     * Uploader factory
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    private $uploaderFactory;
 
    /**
     * Media directory object (writable).
     *
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;
 
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
 
    /**
     * Core file storage database
     *
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $coreFileStorageDatabase;
 
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
 
    /**
     * Upload constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Model\ImageUploader $imageUploader
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Codazon\Lookbookpro\Model\ImageUploader $imageUploader,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDatabase,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->imageUploader = $imageUploader;
        $this->uploaderFactory = $uploaderFactory;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->storeManager = $storeManager;
        $this->coreFileStorageDatabase = $coreFileStorageDatabase;
        $this->logger = $logger;
    }
 
    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Codazon_Lookbookpro::cdzlookbook');
    }
 
    /**
     * Upload file controller action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $data = $this->getRequest()->getParams();
            $id = $data['param_name'];
            $result = $this->imageUploader->saveFileToTmpDir($id);
            $session = $this->_getSession();
            $result['cookie'] = [
                'name'      => $session->getName(),
                'value'     => $session->getSessionId(),
                'lifetime'  => $session->getCookieLifetime(),
                'path'      => $session->getCookiePath(),
                'domain'    => $session->getCookieDomain(),
            ];
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
