<?php
/**
 * Created by PhpStorm.
 * User: won
 * Date: 25/06/2017
 * Time: 21:30
 */

namespace Wn\Modules\Sync\Storage;

use League\Flysystem\Filesystem;
use League\Flysystem\WebDAV\WebDAVAdapter;
use Sabre\DAV\Client;

class WebDavDisk extends AStorage
{

    public function __construct($name, $settings)
    {
        parent::__construct($name, $settings);
        $this->setType('webdav');
        $this->setFlySystem(new Filesystem(new WebDAVAdapter(new Client($this->getSettings()))));

    }
}