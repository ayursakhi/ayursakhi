<?php
namespace Codazon\ThemeOptions\Plugin\Magento\Catalog\Block\Product\View;
class Gallery
{
    public function afterGetGalleryImagesJson($subject, $result)
    {
    	$images = json_decode($result);
        $data = array();
        foreach($images as $img){
            $img->img = $img->full;
            $data[] = $img;
        }
        return json_encode($data);
    }
}
