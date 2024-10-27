<?php
namespace Module\Employees\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Module\Employees\Entity\Employee;

class EmployeeRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Employee::class);
        $this->entityManager = $entityManager;
    }

    /**
     * Сохранить сотрудника в базе данных.
     *
     * @param Employee $employee
     * @param bool $flush
     */
    public function saveEmployee(Employee $employee, bool $flush = false): void
    {
        $this->entityManager->persist($employee);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    /**
     * Удалить сотрудника из базы данных.
     *
     * @param Employee $employee
     * @param bool $flush
     */
    public function deleteEmployee(Employee $employee, bool $flush = false): void
    {
        // Удаление объекта сотрудника из EntityManager
        $this->entityManager->remove($employee);

        // Если параметр flush равен true, выполняем flush для завершения транзакции
        if ($flush) {
            $this->entityManager->flush();
        }
    }


    /**
     * Получить всех сотрудников из базы данных.
     *
     * @return Employee[]
     */
    public function findAllEmployees(): array
    {
        return $this->findAll() ?: [];
    }

    /**
     * Найти одного сотрудника по его ID.
     *
     * @param int $id
     * @return Employee|null
     */
    public function findEmployeeById(int $id): ?Employee
    {
        return $this->find($id);
    }

    /**
     * Обновить данные сотрудника по ID.
     *
     * @param int $id
     * @param array $data
     * @return Employee|null
     */
    public function updateEmployee(int $id, array $data): ?Employee
    {
        $employee = $this->find($id);

        if (!$employee) {
            return null;
        }

        if (isset($data['name'])) {
            $employee->setEmployeeName($data['name']);
        }

        if (isset($data['jobTitle'])) {
            $employee->setEmployeeJobTitle($data['jobTitle']);
        }

        if (isset($data['description'])) {
            $employee->setEmployeeDescription($data['description']);
        }

        if (isset($data['link'])) {
            $employee->setEmployeeLink($data['link']);
        }

        // Добавить обновление других полей по необходимости

        $this->entityManager->persist($employee);
        $this->entityManager->flush();

        return $employee;
    }

    /**
     * Обновить конкретное поле сотрудника по его ID.
     *
     * @param int $id
     * @param string $field
     * @param mixed $value
     * @return Employee|null
     */
    public function updateEmployeeField(int $id, string $field, $value): ?Employee
    {
        $employee = $this->find($id);

        if (!$employee) {
            return null; // Возвращаем null, если сотрудник не найден
        }

        // Генерируем имя сеттера для поля
        $setter = 'set' . ucfirst($field);

        if (method_exists($employee, $setter)) {
            $employee->$setter($value); // Вызываем сеттер с переданным значением
        } else {
            throw new \InvalidArgumentException("Field '$field' does not exist on Employee entity.");
        }

        $this->entityManager->persist($employee);
        $this->entityManager->flush();

        return $employee;
    }


    /**
     * Проверка наличия сотрудников.
     *
     * @return bool
     */
    public function hasEmployees(): bool
    {
        return !empty($this->findAll());
    }

    /**
     * Получить сотрудников по категории.
     *
     * @param int $categoryID
     * @return Employee[]
     */
    public function findEmployeesByCategory(int $categoryID): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.categoryID = :categoryID')
            ->setParameter('categoryID', $categoryID)
            ->orderBy('e.employeeName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Получить сотрудников по языку.
     *
     * @param int $languageID
     * @return Employee[]
     */
    public function findEmployeesByLanguage(int $languageID): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.languageID = :languageID')
            ->setParameter('languageID', $languageID)
            ->orderBy('e.employeeName', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

