<?php

namespace Dev\ProductComments\Block;


use Magento\Framework\View\Element\Template;

class View extends Template
{
    public function __construct(Template\Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }
}