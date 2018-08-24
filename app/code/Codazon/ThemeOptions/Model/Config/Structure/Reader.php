<?php
/**
 * Backend System Configuration reader.
 * Retrieves system configuration form layout from system.xml files. Merges configuration and caches it.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeOptions\Model\Config\Structure;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\TemplateEngine\Xhtml\CompilerInterface;

/**
 * Class Reader
 */
class Reader extends \Magento\Config\Model\Config\Structure\Reader
{
	public function __construct(
        \Codazon\ThemeOptions\Framework\App\Config\FileResolver $fileResolver,
        \Magento\Config\Model\Config\Structure\Converter $converter,
        \Magento\Config\Model\Config\SchemaLocator $schemaLocator,
        \Magento\Framework\Config\ValidationStateInterface $validationState,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        CompilerInterface $compiler,
        $fileName = 'codazon_options.xml',
        $idAttributes = [],
        $domDocumentClass = 'Magento\Framework\Config\Dom',
        $defaultScope = 'global'
    ) {
        $this->compiler = $compiler;
        $this->_objectManager = $objectManager;
        $this->registry = $registry;
        parent::__construct(
            $fileResolver,
            $converter,
            $schemaLocator,
            $validationState,
            $compiler,
            $fileName,
            $idAttributes,
            $domDocumentClass,
            $defaultScope
        );
    }

    public function read($themeId = NULL)
    {
        $scope = 'adminhtml';
        $fileList = $this->_fileResolver->cdzget($this->_fileName, $scope, $themeId);
        if (!count($fileList)) {
            return [];
        }
        $output = $this->_readFiles($fileList);


        return $output;
    }
}
