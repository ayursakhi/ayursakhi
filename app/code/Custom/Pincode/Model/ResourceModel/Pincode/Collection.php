<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Custom\Pincode\Model\ResourceModel\Pincode;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection{

    public function _construct(){
        $this->_init('Custom\Pincode\Model\Pincode','Custom\Pincode\Model\ResourceModel\Pincode');
    }

}
