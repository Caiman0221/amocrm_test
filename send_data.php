<?php 

use AmoCRM\Collections\ContactsCollection;
use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Collections\Leads\LeadsCollection;
use AmoCRM\Collections\TagsCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Models\CompanyModel;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\CustomFieldsValues\MultitextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\MultitextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\MultitextCustomFieldValueModel;

use AmoCRM\Models\CustomFieldsValues\TextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\TextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\TextCustomFieldValueModel;

use AmoCRM\Models\LeadModel;
use AmoCRM\Models\TagModel;
use AmoCRM\Models\Unsorted\FormsMetadata;
use League\OAuth2\Client\Token\AccessTokenInterface;

include_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/amocrm/amocrm-api-library/examples/bootstrap.php';

// вывод ошибок 
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);

// получаем значения из формы на сайте
$_POST = json_decode(file_get_contents("php://input"), true);

// проверяем как долго пользователь был на сайте
if (time() - $_POST['in_note'] > 30) $note = 'Пользователь долго был на сайте';
else $note = 'Пользователь быстро покинул сайт';

//собираем все данные
$externalData = [
    'is_new' => true,
    'price' => $_POST['in_cost'],
    'name' => 'Сделка века',
    'contact' => [
        'first_name' => $_POST['in_name'],
        'phone' => $_POST['in_phone'],
        'email' => $_POST['in_email'],
        'time' => $note
    ],
    'company' => [
        'name' => 'Some CO',
    ],
    'tag' => 'Новый клиент',
    'external_id' => '0752a617-c834-4bde-b4a6-76ff0fe26871',
];

// подключаемся к amocrm
$accessToken = getToken();

$apiClient->setAccessToken($accessToken)
    ->setAccountBaseDomain($accessToken->getValues()['baseDomain'])
    ->onAccessTokenRefresh(
        function (AccessTokenInterface $accessToken, string $baseDomain) {
            saveToken(
                [
                    'accessToken' => $accessToken->getToken(),
                    'refreshToken' => $accessToken->getRefreshToken(),
                    'expires' => $accessToken->getExpires(),
                    'baseDomain' => $baseDomain,
                ]
            );
        }
    );

$leadsCollection = new LeadsCollection();

//Создадим модели и заполним ими коллекцию
$lead = (new LeadModel())
    ->setName($externalData['name'])
    ->setPrice($externalData['price'])
    ->setTags(
        (new TagsCollection())
            ->add(
                (new TagModel())
                    ->setName($externalData['tag'])
            )
    )
    ->setContacts(
        (new ContactsCollection())
            ->add(
                (new ContactModel())
                    ->setFirstName($externalData['contact']['first_name'])
                    ->setCustomFieldsValues(
                        (new CustomFieldsValuesCollection())
                            ->add(
                                (new MultitextCustomFieldValuesModel())
                                    ->setFieldCode('PHONE')
                                    ->setValues(
                                        (new MultitextCustomFieldValueCollection())
                                            ->add(
                                                (new MultitextCustomFieldValueModel())
                                                    ->setValue($externalData['contact']['phone'])
                                            )
                                    )
                            )
                            ->add(
                                (new MultitextCustomFieldValuesModel())
                                    ->setFieldCode('EMAIL')
                                    ->setValues(
                                        (new MultitextCustomFieldValueCollection())
                                            ->add(
                                                (new MultitextCustomFieldValueModel())
                                                    ->setValue($externalData['contact']['email'])
                                            )
                                    )
                            )
                            ->add(
                                (new TextCustomFieldValuesModel())
                                    ->setFieldId(149759)
                                    ->setFieldName('t_time')
                                    ->setValues(
                                        (new TextCustomFieldValueCollection())
                                            ->add(
                                                (new TextCustomFieldValueModel())
                                                    ->setValue($externalData['contact']['time'])
                                            )
                                    )
                            )
                    )
            )
    )
    ->setCompany(
        (new CompanyModel())
            ->setName($externalData['company']['name'])
    )
    ->setRequestId($externalData['external_id'])
    ->setMetadata(
        (new FormsMetadata())
            ->setFormId('my_best_form')
            ->setFormName('Обратная связь')
            ->setFormPage('https://stanislavzhmurin.ru')
            ->setFormSentAt(mktime(date('h'), date('i'), date('s'), date('m'), date('d'), date('Y')))
            ->setReferer('https://google.com/search')
            ->setIp('192.168.0.1')
    );

$leadsCollection->add($lead);

//Создадим сделки
try {
    $addedLeadsCollection = $apiClient->leads()->addComplex($leadsCollection);
} catch (AmoCRMApiException $e) {
    printError($e);
    die;
}

/** @var LeadModel $addedLead */
foreach ($addedLeadsCollection as $addedLead) {
    //Пройдемся по добавленным сделкам и выведем результат
    $leadId = $addedLead->getId();
    $contactId = $addedLead->getContacts()->first()->getId();
    $companyId = $addedLead->getCompany()->getId();

    $externalRequestIds = $addedLead->getComplexRequestIds();
    foreach ($externalRequestIds as $requestId) {
        $action = $addedLead->isMerged() ? 'обновлены' : 'созданы';
        $separator = PHP_SAPI === 'cli' ? PHP_EOL : "<br>";
        echo "Для сущности с ID {$requestId} были {$action}: сделка ({$leadId}), контакт ({$contactId}), компания ({$companyId})" . $separator;
    }
}