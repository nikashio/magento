<?php

namespace Dev\ProductComments\Block;

use Magento\Framework\View\Element\Template\Context;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Dev\ProductComments\Model\ResourceModel\Comment\CollectionFactory;

class View extends Template
{
    /**
     * @var Registry
     */
    private $registry;

    protected $commentFactory;

    /**
     * View constructor.
     * @param Template\Context $context
     * @param Registry $registry
     * @param CollectionFactory $commentFactory
     * @param array $data
     */
    public function __construct(Template\Context $context, Registry $registry,CollectionFactory $commentFactory, array $data = [])
    {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->commentFactory = $commentFactory;
    }

    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }


    public function getCommentCollection($productId)
    {
        $comment = $this->commentFactory->create();
        $collection = $comment
            ->addFieldToFilter("product_id", $productId)
            ->addFieldToFilter("status", "approved")
            ->getItems();
        return $collection;
    }




}