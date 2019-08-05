<?php

namespace Dev\ProductComments\Model\ResourceModel\Comment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $idFieldName = 'comment_id';
    protected $eventPrefix = 'product_comments_collection';
    protected $eventObject = 'comment_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function construct()
    {
        $this->_init(
            'Dev\ProductComments\Model\Comment',
            'Dev\ProductComments\Model\ResourceModel\Comment'
        );
    }

}