<?php

namespace Module\{{ENTITY_DIR}}\{{ENTITY_NAME}}\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Module\{{ENTITY_DIR}}\{{ENTITY_NAME}}\Entity\{{ENTITY_NAME}};

/**
 * @extends ServiceEntityRepository<{{ENTITY_NAME}}>
 */
class {{ENTITY_NAME}}Repository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, {{ENTITY_NAME}}::class);
        $this->entityManager = $entityManager;
    }

    public function save{{ENTITY_NAME_ONE}}({{ENTITY_NAME}} ${{ENTITY_NAME_LOWER}}, bool $flush = false): void
    {
        $this->entityManager->persist(${{ENTITY_NAME_LOWER}});
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function delete{{ENTITY_NAME_ONE}}({{ENTITY_NAME}} ${{ENTITY_NAME_LOWER}}, bool $flush = false): void
    {
        $this->entityManager->remove(${{ENTITY_NAME_LOWER}});
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function find{{ENTITY_NAME_ONE}}By{{ENTITY_CODE_LINK}}(string ${{ENTITY_NAME_LOWER}}): ?{{ENTITY_NAME}}
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.{{ENTITY_NAME_LOWER}}{{ENTITY_CODE_LINK}} = :{{ENTITY_CODE_LINK_LOWER}}')
            ->setParameter('{{ENTITY_CODE_LINK_LOWER}}', ${{ENTITY_NAME_LOWER}})
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAll{{ENTITY_NAME}}(): array
    {
        return $this->findAll() ?: [];
    }

    public function find{{ENTITY_NAME_ONE}}ById(int $id): ?{{ENTITY_NAME}}
    {
        return $this->find($id);
    }

    public function has{{ENTITY_NAME}}(): bool
    {
        return !empty($this->findAll());
    }
}
