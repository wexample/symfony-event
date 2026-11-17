<?php

namespace Wexample\SymfonyEvent\Repository;

use Wexample\SymfonyEvent\Entity\Event;
use Wexample\SymfonyEvent\Entity\Traits\Manipulator\EventEntityManipulatorTrait;
use Wexample\SymfonyHelpers\Repository\AbstractRepository;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event      saveNewEvent(string $title)
 */
class EventRepository extends AbstractRepository
{
    use EventEntityManipulatorTrait;

    public function createNewEvent(string $title): Event
    {
        return new Event($title);
    }

    public function findOneByExternalReference(string $externalReference): ?Event
    {
        return $this->findOneBy(['externalReference' => $externalReference]);
    }

    /**
     * The whole events of a kind — not their stages —, the latest first.
     *
     * @return list<Event>
     */
    public function findByKind(string $kind): array
    {
        return $this->createQueryBuilder('event')
            ->where('event.kind = :kind')
            ->andWhere('event.parent IS NULL')
            ->setParameter('kind', $kind)
            ->orderBy('event.dateStart', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
