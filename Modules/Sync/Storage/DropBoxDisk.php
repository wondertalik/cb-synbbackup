<?php
/**
 * Created by PhpStorm.
 * User: won
 * Date: 6/9/18
 * Time: 1:46 PM
 */

namespace Wn\Modules\Sync\Storage;

use League\Flysystem\Filesystem;
use Srmklive\Dropbox\Adapter\DropboxAdapter;
use Srmklive\Dropbox\Client\DropboxClient;


class DropBoxDisk extends AStorage
{
    public function __construct($name, $settings)
    {
        parent::__construct($name, $settings);

        $this->setType('dropbox');
        $this->setFlySystem(new Filesystem(new DropboxAdapter(new DropboxClient($this->getToken()))));
    }

    public function getToken()
    {
        return $this->getSettings()['token'];
    }
}