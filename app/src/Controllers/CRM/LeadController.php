<?php

namespace Src\Controllers\CRM;

use AmoCRM\Collections\NotesCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Helpers\EntityTypesInterface;
use AmoCRM\Models\LeadModel;
use AmoCRM\Models\NoteType\ServiceMessageNote;
use Src\Controllers\ApiController;
use Src\Helpers\LeadDiffHelper;

/**
 * Контроллер для обработки запросов по лиду
 */
class LeadController
{
    /**
     * Обработчик собыия при создании лида
     * @param int $id
     * @return void
     */
    public function onLeadCreate(int $id): void
    {
        $lead = $this->getLeadFields($id);

        $contactId = $this->getLeadContactId($lead);

        // Создание текстового примечания
        $noteText = "Добавлена сделка '{$lead->getName()} / Контакт:{$contactId}' (ID: {$lead->getId()}), ответственный: {$lead->getResponsibleUserId()}, createdAt: " . $lead->getCreatedAt();

        $this->addLeadNote($noteText, $lead->getId());

        LeadDiffHelper::serializeObjectInFile($lead);
    }
    /**
     * Обработчик собыия при обновлении информации лида
     * @param int $id
     * @throws \Exception
     * @return void
     */
    public function onLeadUpdate(int $id): void
    {
        $lead = $this->getLeadFields($id);
        $contactId = $this->getLeadContactId($lead);

        $oldLead = LeadDiffHelper::getObjectFromArray($lead->getId());
        if (!$oldLead) {
            throw new \Exception('Не удалось получить поля старого лида');
        }

        //Сравниваем новый и старый объект лида
        $leadDiff = LeadDiffHelper::compareObjects($oldLead, $lead);
        //Удаляем старый лид
        LeadDiffHelper::deleteObjectFromFile($lead->getId());
        //Записываем обновленный лид
        LeadDiffHelper::serializeObjectInFile($lead);

        $diffText = '';
        //Записываем обновленные атрибуты в строку
        foreach ($leadDiff as $key => $item) {
            $diffText = $diffText . ' ' . $key . ': ' . $item['new'] . ',';
        }

        $noteText = "Обновлена сделка '{$lead->getName()}/Контакт: {$contactId}' \n
        время: {$lead->getUpdatedAt()}, 
        Измененные поля: {$diffText}";

        $this->addLeadNote($noteText, $lead->getId());
    }

    /**
     * Возвращает лида по id
     * @param int $id
     * @return \AmoCRM\Models\LeadModel|null
     */
    public function getLeadFields(int $id): LeadModel|null
    {
        $apiClient = ApiController::getApiClient();

        try {
            $lead = $apiClient->leads()->getOne($id, [LeadModel::CONTACTS]);
        } catch (AmoCRMApiException $e) {
            file_put_contents('log.txt', json_encode($e->getLastRequestInfo()), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | FILE_APPEND);
            return null;
        }
        return $lead;
    }
    /**
     * Возвращает id первого контакта лида
     * @param \AmoCRM\Models\LeadModel $lead
     * @return int|string
     */
    public function getLeadContactId(LeadModel $lead): string|int
    {
        $contacts = $lead->getContacts();
        $contactId = '';
        if (empty($contacts)) {
            $contactId = 'Не указан';
        } else {
            $contactId = $contacts->first()->getId();
        }
        if (empty($contactId)) {
            $contactId = 'Не указан';
        }
        return $contactId;
    }

    /**
     * Добавляет текстовое примечание к лиду
     * @param string $noteText текст примечания
     * @param int $leadId
     * @return void
     */
    public function addLeadNote(string $noteText, int $leadId): void
    {

        $apiClient = ApiController::getApiClient();

        $notesCollection = new NotesCollection();
        $serviceMessageNote = new ServiceMessageNote();
        $serviceMessageNote->setText($noteText)->setEntityId($leadId);
        $serviceMessageNote->setService('common');

        $notesCollection->add($serviceMessageNote);

        try {
            $leadNotesService = $apiClient->notes(EntityTypesInterface::LEADS);
            $notesCollection = $leadNotesService->add($notesCollection);

        } catch (AmoCRMApiException $e) {
            file_put_contents('log.txt', $e->getLastRequestInfo(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | FILE_APPEND);
            die;
        }
    }
}