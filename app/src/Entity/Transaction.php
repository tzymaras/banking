<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TransactionRepository::class)
 */
class Transaction
{
    const TRANSACTION_TYPE_INCOME  = 'income';
    const TRANSACTION_TYPE_EXPENSE = 'expense';

    const TRANSACTION_WORKFLOW_INTERNAL = 'internal';
    const TRANSACTION_WORKFLOW_EXTERNAL = 'external';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $amount;
    /**
     * @var string $formattedAmount
     */
    private $formattedAmount;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $reference;

    /**
     * @ORM\Column(type="datetime_immutable", options={"default": "CURRENT_TIMESTAMP"}, nullable=true)
     */
    private $created_at;

    /**
     * value used internally to determine transaction type (income or expense)
     *
     * @var string $type
     */
    private $type;

    /**
     * 'internal' when transaction is between 2 known/existing accounts, 'external' otherwise
     * 
     * @ORM\Column(type="string", length=255)
     */
    private $workflowType;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ibanFrom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ibanTo;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getWorkflowType(): ?string
    {
        return $this->workflowType;
    }

    public function setWorkflowType(string $workflowType): self
    {
        $this->workflowType = $workflowType;

        return $this;
    }

    /**
     * @return string
     */
    public function getIbanFrom(): string
    {
        return $this->ibanFrom;
    }

    /**
     * @param string $ibanFrom
     * @return $this
     */
    public function setIbanFrom(string $ibanFrom): self
    {
        $this->ibanFrom = $ibanFrom;
        return $this;
    }

    /**
     * @return string
     */
    public function getIbanTo(): string
    {
        return $this->ibanTo;
    }

    /**
     * @param string $ibanTo
     * @return $this
     */
    public function setIbanTo(string $ibanTo): self
    {
        $this->ibanTo = $ibanTo;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormattedAmount(): string
    {
        return $this->formattedAmount;
    }

    /**
     * @param string $formattedAmount
     * @return Transaction
     */
    public function setFormattedAmount(string $formattedAmount): Transaction
    {
        $this->formattedAmount = $formattedAmount;
        return $this;
    }
}
