<?php
/**
 * Created by PhpStorm.
 * User: won
 * Date: 25/06/2017
 * Time: 21:13
 */

namespace Wn\Modules\Sync\Storage;

use Exception;
use League\Flysystem\Filesystem;
use Wn\Modules\CustomLogger;

abstract class AStorage
{
    /**
     * @var CustomLogger
     */
    protected $logger;
    protected $settings;
    protected $type;

    /**
     * @var Filesystem
     */
    private $flySystem;

    /**
     * AStorage constructor.
     * @param $name
     * @param $settings
     * @throws \Exception
     */
    public function __construct($name, $settings)
    {
        $this->settings = $settings;
        $this->logger = new CustomLogger($name);
    }

    /**
     * Возвращает локальный путь откуда копировать
     * @return string|null
     */
    protected function getLocalPath()
    {
        if(isset($this->settings['localPath']))
            return $this->settings['localPath'];
        return null;
    }

    protected function getMaxCountFiles()
    {
        if(isset($this->settings['maxCountFiles']))
            return $this->settings['maxCountFiles'];
        return 0;
    }

    /**
     * Возвращает удаленный путь куда копировать
     * @return string|null
     */
    protected function getSyncPath()
    {
        if(isset($this->settings['syncPath']))
            return $this->settings['syncPath'];
        return null;
    }

    /**
     * Возвращает количество дней которые нужно сохранить
     * @return int
     */
    protected function getSaveDays()
    {
        if(isset($this->settings['saveDays']))
            return $this->settings['saveDays'];
        return 2;
    }

    /**
     * @return Filesystem
     */
    public function getFlySystem()
    {
        return $this->flySystem;
    }

    /**
     * @param Filesystem $flySystem
     * @return AStorage
     */
    public function setFlySystem(Filesystem $flySystem)
    {
        $this->flySystem = $flySystem;
        return $this;
    }

    /**
     * @return CustomLogger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return AStorage
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    protected function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param $fileList
     * @return bool
     * @throws \League\Flysystem\FileExistsException
     */
    public function copyFilesToDisk($fileList)
    {
        $st = false;

        foreach($fileList as $file) {

            $syncPathFile = "{$this->getSyncPath()}/{$file}";
            $localPathFile = "{$this->getLocalPath()}/{$file}";

            //если файл существует, не трогаем его
            if(!$this->getFlySystem()->has($syncPathFile)) {
                $this->logger->info("{$localPathFile} upload have started");

                $stream = fopen($localPathFile, 'r+');
                $st = $this->getFlySystem()->writeStream($syncPathFile, $stream);

                if(is_resource($stream)) {
                    fclose($stream);
                }

                $this->logger->info("{$file} status: {$st}");

                //останавливаем, не удалось скопировать
                if(!$st)
                    break;

            } else {
                $this->logger->info("{$syncPathFile} have already exists");
                $st = true;
            }
        }

        return $st;
    }

    /**
     * @return bool
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function deleteFilesFromRemoteDisk()
    {
        $st = false;

        //Получаем файлы из удаленного диска
        $fileList = [];
        $filesBackupDisk = $this->flySystem->listContents($this->getSyncPath());
        foreach($filesBackupDisk as $file) {
            $fileList[] = $file['basename'];
        }

        $this->getLogger()->info("list of the files from remote disk", $fileList);

        //исключаем файлы которые скопировали
        $listToDelete = array_diff($fileList, $this->getBackupListFiles());
        $this->getLogger()->info("Need delete files from remote disk " . count($listToDelete) . ")", $listToDelete);

        if(count($listToDelete) == 0) {
            //удалять ничего не нужно, очищаем список файлов
            $st = true;
        }

        foreach($listToDelete as $file) {
            $st = $this->getFlySystem()->delete("{$this->getSyncPath()}/{$file}");
            $this->logger->info("{$this->getSyncPath()}/{$file} deleted: {$st}");

            //ошибка удаления
            if(!$st)
                break;
        }


        return $st;
    }

    /**
     * Удаляем файлы с локального диска
     * @return bool
     */
    public function deleteFilesFromLocalDisk()
    {
        $st = false;
        $this->getLogger()->info("list of the files from local disk", $this->getFullBackupListFiles());

        //исключаем файлы которые скопировали
        $listToDelete = array_diff($this->getFullBackupListFiles(), $this->getBackupListFiles());
        $this->logger->info("Need delete files from local disk", $listToDelete);

        foreach($listToDelete as $file) {
            $st = unlink("{$this->getLocalPath()}/{$file}");
            $this->logger->info("{$this->getLocalPath()}/{$file} deleted: {$st}");
            if(!$st)
                break;
        }
        return $st;
    }

    /**
     * Возвращает полный список файлов
     * @return array
     */
    public function getFullBackupListFiles()
    {

        $backupFiles = [];
        //фильтруем файлы бэкапа
        $listFromBackup = scandir($this->getLocalPath());
        rsort($listFromBackup);

        //последний файл не копируем, чтобы не получить битый бэкап
        $countFileForBackup = count($listFromBackup) - 1;
        for($i = 0; $i < $countFileForBackup; $i++) {
            $file = $listFromBackup[$i];
            if(is_file("{$this->getLocalPath()}/{$file}") && preg_match('/.+\\.zip$/', $file)) {
                array_push($backupFiles, $file);
            }
        }
        return $backupFiles;
    }

    /**
     * Возвращает список файлов бэкапа с учетом максимального количества копируемых файлов
     * @return array
     */
    public function getBackupListFiles()
    {
        //фильтруем файлы бэкапа
        $listFromBackup = $this->getFullBackupListFiles();
        if($this->getMaxCountFiles() != 0 && count($listFromBackup) > $this->getMaxCountFiles())
            return array_slice($listFromBackup, 0, $this->getMaxCountFiles());
        return $listFromBackup;
    }

    /**
     * @throws \League\Flysystem\FileExistsException
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function run()
    {
        try {

            $this->logger->info("{$this->getType()} have started");
            //получаем список файлов, которые нужно скопировать
            $backupFiles = $this->getBackupListFiles();
            $this->logger->info("backupfiles (" . count($backupFiles) . ")", $backupFiles);

            if($this->copyFilesToDisk($backupFiles)) {
                $this->logger->info('all files uploaded');

//                чистим старые бэкапы

//                получем список файлов из удаленного диска
                if($this->deleteFilesFromRemoteDisk()) {
                    $this->logger->info('all files from remote disk deleted');
                    if($this->deleteFilesFromLocalDisk()) {
                        $this->logger->info('all files from local disk deleted');
                    }
                } else {
                    $this->logger->error('files were not successfully deleted');
                }
            };

            $this->logger->info('sync done');

        } catch(Exception $e) {
            $this->logger->error($e->getMessage());
//            $this->logger->error($e->getTraceAsString());
        }
    }

}