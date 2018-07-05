<?php
/**
 * @copyright (c) 2018, sunny-daisy 
 * all rights reserved.
 *
 * Daisy\SnowFlake\SnowFlake phpunit test
 *
 * @author liyong5@staff.sina.com.cn wenqiang1@staff.sina.com.cn
 *
 * @createdate 2018-06-28
 */
use PHPUnit\Framework\TestCase;

class SnowFlakeTest extends TestCase
{
    public function testNextId()
    {
        for ($i = 0; $i < 100000; $i++) {
            $snowflake = Daisy\SnowFlake\SnowFlake::getInstance();
            $data[] = $snowflake->nextId();
        }
        $data = array_unique($data);
        $this->assertEquals(100000, sizeof($data));
    }

}

