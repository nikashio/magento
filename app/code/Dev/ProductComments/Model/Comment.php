<?php

namespace Dev\ProductComments\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Dev\ProductComments\Model\ResourceModel\Comment as ResourceComment;

class Comment extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'product_comments';

    protected $cacheTag = 'product_comments';

    protected $eventPrefix = 'product_comments';

    protected function _construct()
    {
        $this->_init(ResourceComment::class);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
