<?php

namespace Wexample\SymfonyEvent\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Wexample\SymfonyEvent\Repository\EventRepository;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyHelpers\Entity\Traits\HasTitleTrait;

/**
 * Something that lasts over a period, as schema.org's Event: a presidential
 * election, a party congress, a parliamentary term, a campaign. It may be cut into
 * stages, events of their own under it — the election into its two rounds.
 */
#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\Table(name: 'event')]
#[ORM\Index(columns: ['kind'])]
#[ORM\Index(columns: ['date_start'])]
class Event extends AbstractEntity implements Stringable
{
    use HasTitleTrait;

    /**
     * What kind of event it is, in the application's words: `election`, `round`,
     * `congress`, `term`.
     */
    #[ORM\Column(type: Types::STRING, length: 32, nullable: true)]
    protected ?string $kind = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $description = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    protected ?DateTimeImmutable $dateStart = null;

    /** The same as the start for a single day; null while it lasts or is to come. */
    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    protected ?DateTimeImmutable $dateEnd = null;

    /** The event it is a stage of; null for a whole one. */
    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'stages')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    protected ?self $parent = null;

    /** @var Collection<int, self> Its stages, in their order. */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent')]
    #[ORM\OrderBy(['dateStart' => 'ASC'])]
    protected Collection $stages;

    /**
     * Its key where it was imported from (`wikidata:Q…`): an import finds by it
     * what it already brought.
     */
    #[ORM\Column(type: Types::STRING, length: 255, unique: true, nullable: true)]
    protected ?string $externalReference = null;

    public function __construct(string $title)
    {
        parent::__construct();

        $this->setTitle($title);
        $this->stages = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->title;
    }

    public function getKind(): ?string
    {
        return $this->kind;
    }

    public function setKind(?string $kind): static
    {
        $this->kind = $kind;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDateStart(): ?DateTimeImmutable
    {
        return $this->dateStart;
    }

    public function getDateEnd(): ?DateTimeImmutable
    {
        return $this->dateEnd;
    }

    public function setPeriod(
        ?DateTimeImmutable $dateStart,
        ?DateTimeImmutable $dateEnd
    ): static {
        $this->dateStart = $dateStart;
        $this->dateEnd = $dateEnd;

        return $this;
    }

    /**
     * Whether it was under way on $date. An unknown start or end does not exclude it.
     */
    public function isActiveAt(DateTimeInterface $date): bool
    {
        return (null === $this->dateStart || $this->dateStart <= $date)
            && (null === $this->dateEnd || $this->dateEnd >= $date);
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    /**
     * Both sides kept in step, so that an event reads right before the flush.
     */
    public function setParent(?self $parent): static
    {
        $this->parent?->stages->removeElement($this);
        $this->parent = $parent;
        $parent?->stages->add($this);

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getStages(): Collection
    {
        return $this->stages;
    }

    public function getExternalReference(): ?string
    {
        return $this->externalReference;
    }

    public function setExternalReference(?string $externalReference): static
    {
        $this->externalReference = $externalReference;

        return $this;
    }
}
