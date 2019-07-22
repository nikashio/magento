<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\ResourceModel\Shipment;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\ShipmentTrackRepositoryInterface;
use Temando\Shipping\Api\Data\Shipment\ShipmentReferenceInterface;
use Temando\Shipping\Api\Data\Shipment\ShipmentReferenceInterfaceFactory;
use Temando\Shipping\Model\ResourceModel\Repository\ShipmentRepositoryInterface;
use Temando\Shipping\Model\ResourceModel\Shipment\ShipmentReference as ShipmentReferenceResource;
use Temando\Shipping\Model\ShipmentInterface;
use Temando\Shipping\Rest\Adapter\ShipmentApiInterface;
use Temando\Shipping\Rest\EntityMapper\ShipmentResponseMapper;
use Temando\Shipping\Rest\Exception\AdapterException;
use Temando\Shipping\Rest\Request\ItemRequestInterfaceFactory;

/**
 * Temando Shipment Repository
 *
 * @package Temando\Shipping\Model
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author  Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class ShipmentRepository implements ShipmentRepositoryInterface
{
    /**
     * @var ShipmentApiInterface
     */
    private $apiAdapter;

    /**
     * @var ItemRequestInterfaceFactory
     */
    private $requestFactory;

    /**
     * @var ShipmentResponseMapper
     */
    private $shipmentMapper;

    /**
     * @var ShipmentReferenceResource
     */
    private $resource;

    /**
     * @var ShipmentReferenceInterfaceFactory
     */
    private $shipmentReferenceFactory;

    /**
     * @var ShipmentReferenceCollectionFactory
     */
    private $shipmentReferenceCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var ShipmentTrackRepositoryInterface
     */
    private $shipmentTrackRepository;

    /**
     * ShipmentRepository constructor.
     * @param ShipmentApiInterface $apiAdapter
     * @param ItemRequestInterfaceFactory $requestFactory
     * @param ShipmentResponseMapper $shipmentMapper
     * @param ShipmentReference $resource
     * @param ShipmentReferenceInterfaceFactory $shipmentReferenceFactory
     * @param ShipmentReferenceCollectionFactory $shipmentReferenceCollectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param FilterBuilder $filterBuilder
     * @param ShipmentTrackRepositoryInterface $shipmentTrackRepository
     */
    public function __construct(
        ShipmentApiInterface $apiAdapter,
        ItemRequestInterfaceFactory $requestFactory,
        ShipmentResponseMapper $shipmentMapper,
        ShipmentReferenceResource $resource,
        ShipmentReferenceInterfaceFactory $shipmentReferenceFactory,
        ShipmentReferenceCollectionFactory $shipmentReferenceCollectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        FilterBuilder $filterBuilder,
        ShipmentTrackRepositoryInterface $shipmentTrackRepository
    ) {
        $this->apiAdapter = $apiAdapter;
        $this->requestFactory = $requestFactory;
        $this->shipmentMapper = $shipmentMapper;
        $this->resource = $resource;
        $this->shipmentReferenceFactory = $shipmentReferenceFactory;
        $this->shipmentReferenceCollectionFactory = $shipmentReferenceCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->filterBuilder = $filterBuilder;
        $this->shipmentTrackRepository = $shipmentTrackRepository;
    }

    /**
     * Load external shipment entity from platform.
     *
     * @param string $shipmentId
     * @return ShipmentInterface
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getById($shipmentId)
    {
        if (!$shipmentId) {
            throw new LocalizedException(__('An error occurred while loading data.'));
        }

        try {
            $request = $this->requestFactory->create(['entityId' => $shipmentId]);
            $apiShipment = $this->apiAdapter->getShipment($request);
            $shipment = $this->shipmentMapper->map($apiShipment);
        } catch (AdapterException $e) {
            if ($e->getCode() === 404) {
                throw NoSuchEntityException::singleField('shipmentId', $shipmentId);
            }

            throw new LocalizedException(__('An error occurred while loading data.'), $e);
        }

        return $shipment;
    }

    /**
     * Load local track info.
     *
     * @param string $carrierCode
     * @param string $trackingNumber
     * @return \Magento\Sales\Api\Data\ShipmentTrackInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getShipmentTrack($carrierCode, $trackingNumber)
    {
        $numberFilter = $this->filterBuilder
            ->setField('track_number')
            ->setValue($trackingNumber)
            ->setConditionType('eq')
            ->create();
        $carrierFilter = $this->filterBuilder
            ->setField('carrier_code')
            ->setValue($carrierCode)
            ->setConditionType('eq')
            ->create();

        // builder does not get reset properly on `create()`, instantiate a fresh one…
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteria = $searchCriteriaBuilder
            ->addFilter($numberFilter)
            ->addFilter($carrierFilter)
            ->addSortOrder('entity_id', SortOrder::SORT_DESC)
            ->setPageSize(1)
            ->create();

        /** @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection $shipmentTracksCollection */
        $shipmentTracksCollection = $this->shipmentTrackRepository->getList($searchCriteria);
        /** @var \Magento\Sales\Model\Order\Shipment\Track $shipmentTrack */
        $shipmentTrack = $shipmentTracksCollection->fetchItem();
        if (!$shipmentTrack) {
            throw NoSuchEntityException::singleField('track_number', $trackingNumber);
        }

        return $shipmentTrack;
    }

    /**
     * @param ShipmentReferenceInterface $shipment
     * @return ShipmentReferenceInterface
     * @throws CouldNotSaveException
     */
    public function saveReference(ShipmentReferenceInterface $shipment)
    {
        try {
            /** @var \Temando\Shipping\Model\Shipment\ShipmentReference $shipment */
            $this->resource->save($shipment);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('Unable to save shipment reference.'), $exception);
        }
        return $shipment;
    }

    /**
     * @param int $entityId
     * @return ShipmentReferenceInterface
     * @throws NoSuchEntityException
     */
    public function getReferenceById($entityId)
    {
        /** @var \Temando\Shipping\Model\Shipment\ShipmentReference $shipment */
        $shipment = $this->shipmentReferenceFactory->create();
        $this->resource->load($shipment, $entityId);

        if (!$shipment->getId()) {
            throw new NoSuchEntityException(__('Shipment with id "%1" does not exist.', $entityId));
        }

        return $shipment;
    }

    /**
     * @param int $shipmentId
     * @return ShipmentReferenceInterface
     * @throws NoSuchEntityException
     */
    public function getReferenceByShipmentId($shipmentId)
    {
        $entityId = $this->resource->getIdByShipmentId($shipmentId);
        return $this->getReferenceById($entityId);
    }

    /**
     * Load local reference to external shipment entity by Temando shipment ID.
     *
     * @param string $extShipmentId
     *
     * @return \Temando\Shipping\Api\Data\Shipment\ShipmentReferenceInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getReferenceByExtShipmentId($extShipmentId)
    {
        $entityId = $this->resource->getIdByExtShipmentId($extShipmentId);

        return $this->getReferenceById($entityId);
    }

    /**
     * Load local reference to external shipment entity by Temando return shipment ID.
     *
     * @param string $extShipmentId
     *
     * @return \Temando\Shipping\Api\Data\Shipment\ShipmentReferenceInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getReferenceByExtReturnShipmentId($extShipmentId)
    {
        $entityId = $this->resource->getIdByExtReturnShipmentId($extShipmentId);

        return $this->getReferenceById($entityId);
    }

    /**
     * List shipment references that match specified search criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return ShipmentReferenceCollection
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->shipmentReferenceCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        return $collection;
    }
}
