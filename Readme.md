Используется для копирования бэкапов на внешний файловый сервис

```
require_once "init.php";

        $settings = [
            'webdav' => [
                'baseUri' => 'https://webdav.yandex.ru',
                'userName' => 'email',
                'password' => 'password',
                'localPath' => '/var/www/backup', //откуда нужно копировать
                'syncPath' => 'backup', // путь в внешнем файловом сервисе
                'maxCountFiles' => 0 // максимальное кол-во файлов которое можно скопировать

            ],
            'dropbox' => [
                'token' => 'dropbox-token',
                'localPath' => '/var/www/backup', //откуда нужно копировать
                'syncPath' => 'backup', // путь в внешнем файловом сервисе
                'maxCountFiles' =>  13
            ]
        ];
        
$sync = new \Wn\Modules\Sync\Synchronizer("hogwarts", $settings);
$sync->run();
```