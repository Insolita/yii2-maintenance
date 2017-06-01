<?php
/**
 * Created by solly [01.06.17 4:32]
 */

namespace insolita\maintenance;

/**
 * Interface IConfig
 *
 * @package insolita\maintenance
 */
interface IConfig
{
    /**
     * @param $key
     * @param $default
     *
     * @return mixed
     */
    public function get($key, $default);
}
