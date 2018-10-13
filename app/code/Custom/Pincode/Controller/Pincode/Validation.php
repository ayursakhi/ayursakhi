<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Custom\Pincode\Controller\Pincode;

use Magento\Framework\App\Action\Context;

class Validation extends \Magento\Framework\App\Action\Action{

    protected $pincode;

    public function __construct(\Custom\Pincode\Block\Pincode $pincode,Context $context){
        $this->pincode = $pincode;
        parent::__construct($context);
    }

    public function execute(){
        $data = $this->getRequest()->getParams();

        $response = "";

        $pincode = $data['pincode'];
        if ($pincode){
            try{
                $response = $this->pincode->checkDelivery($pincode);
                echo $response;
                exit;
            }
            catch (Exception $e){
                $response = "Please Enter Valid Pincode";
            }
        }
        else{
            $response = "Please Enter Valid Pincode";
        }
        return $response;
    }

}
