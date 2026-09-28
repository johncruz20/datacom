<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Test\Unit\Model\Queue;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use PawsWhiskers\PetProfile\Api\Data\PetInterface;
use PawsWhiskers\PetProfile\Api\Data\PetSyncMessageInterface;
use PawsWhiskers\PetProfile\Api\MarketingSyncInterface;
use PawsWhiskers\PetProfile\Api\PetRepositoryInterface;
use PawsWhiskers\PetProfile\Model\Queue\PetSyncMessage;
use PawsWhiskers\PetProfile\Model\Queue\SyncConsumer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \PawsWhiskers\PetProfile\Model\Queue\SyncConsumer
 */
class SyncConsumerTest extends TestCase
{
    /**
     * @var PetRepositoryInterface&MockObject
     */
    private PetRepositoryInterface $petRepository;

    /**
     * @var MarketingSyncInterface&MockObject
     */
    private MarketingSyncInterface $marketingSync;

    /**
     * @var LoggerInterface&MockObject
     */
    private LoggerInterface $logger;

    /**
     * @var SyncConsumer
     */
    private SyncConsumer $consumer;

    protected function setUp(): void
    {
        $this->petRepository = $this->createMock(PetRepositoryInterface::class);
        $this->marketingSync = $this->createMock(MarketingSyncInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->consumer = new SyncConsumer($this->petRepository, $this->marketingSync, $this->logger);
    }

    public function testUpsertSyncsCurrentPetState(): void
    {
        $pet = $this->createMock(PetInterface::class);
        $this->petRepository->expects($this->once())->method('getById')->with(5)->willReturn($pet);
        $this->marketingSync->expects($this->once())->method('upsert')->with($pet);

        $this->consumer->process($this->createMessage(PetSyncMessageInterface::OPERATION_UPSERT));
    }

    public function testUpsertForDeletedPetIsSkipped(): void
    {
        $this->petRepository->method('getById')->willThrowException(new NoSuchEntityException());
        $this->marketingSync->expects($this->never())->method('upsert');
        $this->logger->expects($this->never())->method('error');

        $this->consumer->process($this->createMessage(PetSyncMessageInterface::OPERATION_UPSERT));
    }

    public function testDeleteRemovesPetWithoutLoadingIt(): void
    {
        $this->petRepository->expects($this->never())->method('getById');
        $this->marketingSync->expects($this->once())->method('remove')->with(42, 5);

        $this->consumer->process($this->createMessage(PetSyncMessageInterface::OPERATION_DELETE));
    }

    public function testAdapterFailureIsLoggedAndRethrownForRetry(): void
    {
        $this->petRepository->method('getById')->willReturn($this->createMock(PetInterface::class));
        $failure = new LocalizedException(__('Klaviyo returned HTTP 503'));
        $this->marketingSync->method('upsert')->willThrowException($failure);
        $this->logger->expects($this->once())->method('error')
            ->with('Pet profile marketing sync failed', $this->callback(
                fn (array $context) => $context['pet_id'] === 5 && $context['exception'] === $failure
            ));

        $this->expectExceptionObject($failure);

        $this->consumer->process($this->createMessage(PetSyncMessageInterface::OPERATION_UPSERT));
    }

    public function testUnknownOperationIsDroppedWithWarning(): void
    {
        $this->marketingSync->expects($this->never())->method('upsert');
        $this->marketingSync->expects($this->never())->method('remove');
        $this->logger->expects($this->once())->method('warning');

        $this->consumer->process($this->createMessage('rename'));
    }

    /**
     * Create message.
     *
     * @param string $operation
     * @return PetSyncMessageInterface
     */
    private function createMessage(string $operation): PetSyncMessageInterface
    {
        return (new PetSyncMessage())
            ->setPetId(5)
            ->setCustomerId(42)
            ->setOperation($operation)
            ->setOccurredAt('2026-09-28T10:00:00+00:00');
    }
}
