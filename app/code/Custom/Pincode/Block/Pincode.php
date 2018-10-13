<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Custom\Pincode\Block;

class Pincode extends \Magento\Framework\View\Element\Template{

    protected $_storeManager;
    protected $pincodeCheck;

    public function __construct(
    \Custom\Pincode\Model\Pincode $pincodeCheck,\Magento\Framework\View\Element\Template\Context $context,\Magento\Store\Model\StoreManagerInterface $storeManager,array $data = []
    ){
        $this->pincodeCheck = $pincodeCheck;
        $this->_storeManager = $storeManager;
        parent::__construct($context,$data);
    }

    public function checkDelivery($pincode){
        $message = "";
        $model = $this->pincodeCheck->getCollection();
        $results = $model->addFieldToFilter('zipcode',array('eq' => $pincode));

        if (empty($results->getData())){
            $message .= "Please Enter the Valid pincode.";
        }
        else{
            foreach ($results as $zipcodes){
                $pin = $zipcodes->getZipcode();
                if ($pin == $pincode){
                    $message .= "Delivery available in this area.";
                }
                else{
                    $message .= "Currently we don't in this area.";
                }
            }
        }
        return $message;
    }

}
