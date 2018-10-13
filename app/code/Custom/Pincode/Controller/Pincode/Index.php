<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Custom\Pincode\Controller\Pincode;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;

class Index extends \Magento\Framework\App\Action\Action{

    protected $pageFactory;

    public function __construct(PageFactory $pageFactory,Context $context){
        $this->pageFactory = $pageFactory;
        parent::__construct($context);
    }

    public function execute(){
        $pageResult = $this->pageFactory->create();
        return $pageResult;
    }

}
