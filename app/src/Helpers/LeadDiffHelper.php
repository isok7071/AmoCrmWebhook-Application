<?php
namespace Src\Helpers;

use AmoCRM\Models\LeadModel;
use ReflectionClass;

/**
 * Класс с вспомогательными методами для объекта \AmoCRM\Models\LeadModel
 */
class LeadDiffHelper
{
    private const FILENAME = 'obj.txt';

    /**
     * Сериализует объект $lead и записывает его в хранилище
     * @param \AmoCRM\Models\LeadModel $lead
     * @return void
     */
    public static function serializeObjectInFile(LeadModel $lead): void
    {
        file_put_contents(self::FILENAME, serialize([$lead->getId() => $lead]) . PHP_EOL, FILE_APPEND);
    }

    /**
     * Возвращает массив объектов вида 'modelId'=>\AmoCRM\Models\LeadModel=>[]
     * @return array|null
     */
    public static function getObjectsArray(): array|null
    {
        $objArr = [];
        $handle = fopen(self::FILENAME, "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $objArr[] = unserialize($line);
            }
            fclose($handle);
        }
        return $objArr;
    }

    /**
     * Возвращает первый найденный объект \AmoCRM\Models\LeadModel по переданному id
     * @param int $leadId
     * @return \AmoCRM\Models\LeadModel|null
     */
    public static function getObjectFromArray(int $leadId): LeadModel|null
    {
        $objects = self::getObjectsArray();
        $obj = null;
        foreach ($objects as $key => $object) {
            if ($object[$leadId]) {
                $obj = $object[$leadId];
                return $obj;
            }
        }
        return $obj;
    }

    /**
     * Удаляет объект LeadModel из хранилища с объектами
     *
     * @param integer $leadId
     * @return void
     */
    public static function deleteObjectFromFile(int $leadId): void
    {
        $objects = self::getObjectsArray();
        if (empty($objects)) {
            exit();
        }

        foreach ($objects as $key => $object) {
            if ($object ?? [$leadId]) {
                unset($objects[$key][$leadId]);
            }
        }

        self::saveNewObjects($leadId, $objects);
      
    }

    /**
     * Записывает в хранилище массив с лидами
     *
     * @param integer $leadId
     * @return void
     */
    public static function saveNewObjects(int $leadId,array $objects): void
    {
        //Убирает пустые массивы и нумерует массив заново
        $newObjects = array_values(array_filter($objects));

        //Перезаписывает файл
        $file = fopen(self::FILENAME, 'w+');

        foreach ($newObjects as $item) {
            fwrite($file, serialize([$leadId => $item]) . PHP_EOL);
        }
        fclose($file);
    }


    /**
     * Сравнивает два объека лида, и возвращает старое и новое значения поля
     *
     * 
     * @param LeadModel $oldObject
     * @param LeadModel $newObject
     * @return array|null
     */
    public static function compareObjects(LeadModel $oldObject, LeadModel $newObject): array|null
    {
        $differences = [];

        $reflectionClass1 = new ReflectionClass($oldObject);
        $reflectionClass2 = new ReflectionClass($newObject);

        $properties1 = $reflectionClass1->getProperties();

        foreach ($properties1 as $property1) {
            $propertyName = $property1->getName();

            if ($reflectionClass2->hasProperty($propertyName)) {
                $property2 = $reflectionClass2->getProperty($propertyName);
                $property1->setAccessible(true);
                $property2->setAccessible(true);

                $value1 = $property1->getValue($oldObject);
                $value2 = $property2->getValue($newObject);

                if ($value1 !== $value2) {
                    $differences[$propertyName] = [
                        'old' => $value1,
                        'new' => $value2,
                    ];
                }
            }
        }

        return $differences;
    }
}