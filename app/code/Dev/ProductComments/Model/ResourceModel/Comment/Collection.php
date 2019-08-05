<?php

namespace Dev\ProductComments\Model\ResourceModel\Comment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Dev\ProductComments\Model\Comment;
use Dev\ProductComments\Model\ResourceModel\Comment  as ResourceComment;

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
    protected function _construct()
    {
        $this->_init(
            Comment::class,
            ResourceComment::class
        );
    }
}
