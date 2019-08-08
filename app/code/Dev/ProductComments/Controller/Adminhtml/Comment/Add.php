<?php

namespace Dev\ProductComments\Controller\Adminhtml\Comment;

use Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModel;
use Magento\Framework\App\Action\Context;
use Dev\ProductComments\Model\Comment;
use Dev\ProductComments\Model\ResourceModel\Comment as ResourceComment;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Add extends \Dev\ProductComments\Controller\Adminhtml\Comment
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var Comment
     */
    private $commentModel;
    /**
     * @var ResourceModel
     */
    private $resourceModel;

    /**
     * Add constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $coreRegistry
     * @param Comment $commentModel
     * @param ResourceModel $resourceModel
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $coreRegistry,
        Comment $commentModel,
        ResourceModel $resourceModel
    ) {
        parent::__construct($context, $coreRegistry);
        $this->resultPageFactory = $resultPageFactory;
        $this->commentModel = $commentModel;
//        $this->resourceModel = $resourceModel;
    }

    /**
     *
     * @return ResponseInterface|Redirect|ResultInterface|Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('comment_id');
        $model = $this->commentModel;

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager
                    ->addErrorMessage(__('This Comment no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->coreRegistry->register('product_comments', $model);

        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Comment') : __('New Comment'),
            $id ? __('Edit Comment') : __('New Comment')
        );
        $resultPage->getConfig()
            ->getTitle()
            ->prepend(
                $model->getId()
                ? __('Edit Comment %1', $model->getId()) : __('New Comment')
            );
        return $resultPage;
    }
}
