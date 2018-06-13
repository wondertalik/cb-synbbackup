<?php
/**
 * Created by PhpStorm.
 * User: won
 * Date: 25/06/2017
 * Time: 21:33
 */

namespace Wn\Modules;

class Identity
{
    /**
     * Генератор уникального идентификатора
     * @return string
     */
    public function GenIdentity()
    {
        return substr(md5(rand()), 0, 7);
    }
}