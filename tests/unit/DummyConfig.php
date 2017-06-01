<?php
/**
 * Created by solly [01.06.17 7:10]
 */

namespace tests\unit;

class DummyConfig
{
    public function get($key, $default)
    {
        return $default;
    }
    
}
