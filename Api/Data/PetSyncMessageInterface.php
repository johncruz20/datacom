<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Api\Data;

/**
 * Message published to the paws.pet_profile.sync topic.
 *
 * The message intentionally carries identifiers only (no PII). The consumer re-reads the
 * current state, which keeps the queue free of personal data and makes processing idempotent.
 *
 * @api
 */
interface PetSyncMessageInterface
{
    public const OPERATION_UPSERT = 'upsert';
    public const OPERATION_DELETE = 'delete';

    /**
     * Get pet ID.
     *
     * @return int
     */
    public function getPetId(): int;

    /**
     * Set pet ID.
     *
     * @param int $petId
     * @return $this
     */
    public function setPetId(int $petId): self;

    /**
     * Get customer ID.
     *
     * @return int
     */
    public function getCustomerId(): int;

    /**
     * Set customer ID.
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId(int $customerId): self;

    /**
     * One of the OPERATION_* constants.
     *
     * @return string
     */
    public function getOperation(): string;

    /**
     * Set operation.
     *
     * @param string $operation
     * @return $this
     */
    public function setOperation(string $operation): self;

    /**
     * UTC timestamp (ISO-8601) of the change that triggered the message.
     *
     * @return string
     */
    public function getOccurredAt(): string;

    /**
     * Set occurred at.
     *
     * @param string $occurredAt
     * @return $this
     */
    public function setOccurredAt(string $occurredAt): self;
}
