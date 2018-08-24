<?php
namespace Codazon\ThemeOptions\Plugin\Magento\ConfigurableProduct\Helper;
class Data
{
    public function afterGetGalleryImages($subject, $result)
    {
    	$images = $result;
        if ($images instanceof \Magento\Framework\Data\Collection) {
            foreach ($images as $image) {
                /** @var $image \Magento\Catalog\Model\Product\Image */
                $image->setData(
                    'medium_image_url',
                    $image->getData('large_image_url')
                );
            }
        }
        return $images;
    }
}
