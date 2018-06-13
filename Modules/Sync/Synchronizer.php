<?php
/**
 * Created by PhpStorm.
 * User: won
 * Date: 25/06/2017
 * Time: 21:07
 */

namespace Wn\Modules\Sync;


use Wn\Modules\Sync\Storage\AStorage;
use Wn\Modules\Sync\Storage\DropBoxDisk;

class Synchronizer
{
    /** @var AStorage[] */
    protected $storages = [];
    protected $name;
    protected $settings = [];

    /**
     * Synchronizer constructor.
     * @param string $name символьное имя, будет использоваться куда логировать
     * @param $settings
     * @throws \Exception
     */
    public function __construct($name, $settings)
    {
        $this->name = $name;
        $this->settings = $settings;
        $this->init();
    }

    /**
     * @throws \Exception
     */
    protected function init() {
        foreach($this->settings as $name => $setting) {
            switch($name) {
//                case 'webdav':
//                    $this->storages[] = new WebDavDisk($this->name, $setting);
//                    break;
                case 'dropbox':
                    $this->storages[] = new DropBoxDisk($this->name, $setting);
                    break;
            }
        }
    }

    /**
     * @throws \League\Flysystem\FileExistsException
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function run() {
        /** @var AStorage $storage */
        foreach($this->storages as $storage) {
           $storage->run();
       }
    }
}