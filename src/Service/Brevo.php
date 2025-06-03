<?php

namespace App\Service;

use Brevo\Client\Configuration;
use Brevo\Client\Api\ContactsApi;
use GuzzleHttp\Client as GuzzleHttp;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

class Brevo
{
    private string $apiKey;
    private int $defaultListId;
    private ContactsApi $contactsApi;

    public function __construct(string $brevoApiKey, int $brevoDefaultListId = 1)
    {
        $this->apiKey = $brevoApiKey;
        $this->defaultListId = $brevoDefaultListId;

        $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $this->apiKey);
        $this->contactsApi = new ContactsApi(
            new GuzzleHttp(),
            $config
        );
    }

    /**
     * Ajoute un contact à une liste Brevo
     *
     * @param string $email Adresse email du contact
     * @param array $attributes Attributs supplémentaires du contact (optionnel)
     * @param array $listIds Liste des IDs de listes (optionnel, utilise la liste par défaut si non spécifié)
     * @param bool $updateEnabled Si vrai, met à jour le contact s'il existe déjà
     * @return array Réponse de l'API Brevo
     * @throws \RuntimeException En cas d'erreur lors de l'appel à l'API
     */
    public function addContact(
        string $email,
        array $attributes = [],
        ?array $listIds = null,
        bool $updateEnabled = true
    ): void {
        $listIds = $listIds ?? [$this->defaultListId];

        $createContact = new \Brevo\Client\Model\CreateContact();
        $createContact->setEmail($email);
        $createContact->setAttributes($attributes);
        $createContact->setListIds($listIds);
        $createContact->setUpdateEnabled($updateEnabled);

        try {
            $this->contactsApi->createContact($createContact);
        } catch (ExceptionInterface $e) {
            throw new \RuntimeException(
                sprintf('Erreur lors de l\'appel à l\'API Brevo: %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }
}
