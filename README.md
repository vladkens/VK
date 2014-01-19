# VK

## По-русски

Класс реализует VK API и авторизацию по OAuth протоколу.
По всем вопросам можно писать на <vladkens@yandex.ru>

### Использование
1. Подключите класс

        require('VK.php');

2. Создайте объект VK
    1. без авторизации

            $vk = new VK\VK('{APP_ID}', '{API_SECRET}');

    2. с авторизацией

            $vk = new VK\VK('{APP_ID}', '{API_SECRET}', '{ACCESS_TOKEN}');

3. Если нужна авторизация
    1. Получаем ссылку авторизации

            $vk->getAuthorizeURL('{API_SETTINGS}', '{CALLBACK_URL}');

    2. Получаем токен доступа по ключу из ссылки авторизации

            $vk->getAccessToken('{CODE}');
            
    3. Проверить авторизирован ли пользователь
            
            $vk->isAuth(); // return bool

4. Используем API

        $vk->api('{METHOD_NAME}', '{PARAMETERS}');
        
### Другие методы
* Установить версию API.
    `$vk->setApiVersion({NUBMER});`
    
### Переменные
* `{APP_ID}` — ID приложения вконтакте.
* `{API_SECRET}` — Секретный код приложения.
* `{ACCESS_TOKEN}` — Токен доступа.
* `{API_SETTINGS}` — Запрашиваемые [права доступа](http://vk.com/developers.php?oid=-1&p=Права_доступа_приложений) приложения (через запятую).
* `{CALLBACK_URL}` — Адрес, на который будет передан `{CODE}`.
* `{CODE}` — Код для получения токена доступа.
* `{METHOD_NAME}` — Имя API метода. [Все методы](http://vk.com/developers.php?oid=-1&p=Описание_методов_API).
* `{PARAMETERS}` — Параметры соответствующего метода API.

\* Если нужно получить бесконечный токен, используете параметр `offline` в `{API_SETTINGS}`.

### Лицензия
[MIT](https://raw.github.com/vladkens/VK/master/LICENSE)

## English

The PHP class for vk.com API and to support OAuth.
You can ask me any questions by e-mail: <vladkens@yandex.ru>

### Use
1. Connect class

        require('VK.php');
        
2. Create VK object
    1. without authorization

            $vk = new VK\VK('{APP_ID}', '{API_SECRET}');

    2. with authorization

            $vk = new VK\VK('{APP_ID}', '{API_SECRET}', '{ACCESS_TOKEN}');

3. If need authorization
    1. Get authoriz link

            $vk->getAuthorizeURL('{API_SETTINGS}', '{CALLBACK_URL}');

    2. Get the token access by code from the authoriz link

            $vk->getAccessToken('{CODE}');
            
    3. Check the status of authorization
            
            $vk->isAuth(); // return bool
            
4. Usage API

        $vk->api('{METHOD_NAME}', '{PARAMETERS}');

### Other methods
* Set version of API.
    `$vk->setApiVersion({NUBMER});`
    
### Variables
* `{APP_ID}` — Your application's identifier.
* `{API_SECRET}` — Secret application key.
* `{ACCESS_TOKEN}` — Access token.
* `{API_SETTINGS}` —  Access [rights requested](http://vk.com/developers.php?oid=-17680044&p=Application_Access_Rights) by your app (through comma).
* `{CALLBACK_URL}` —  Address to which `{CODE}` will be rendered.
* `{CODE}` — The code to get access token.
* `{METHOD_NAME}` — Name of the API method. [All methods.](http://vk.com/developers.php?oid=-17680044&p=API_Method_Description)
* `{PARAMETERS}` — Parameters of the corresponding API methods.

\* If you need infinite token use key `offline` in `{API_SETTINGS}`.

### License
[MIT](https://raw.github.com/vladkens/VK/master/LICENSE)