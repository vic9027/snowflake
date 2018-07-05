<?php
/**
 * @copyright (c) 2018, sunny-daisy 
 * all rights reserved.
 *
 * a package of php mongodb library
 *
 * @author  liyong5@staff.sina.com.cn wenqiang1@staff.sina.com.cn
 *
 * @createdate  2018-06-28
 */
namespace Daisy\SnowFlake;

final class SnowFlake
{
    const DATACENTER_ID_BITS = 8;
    const WORKER_ID_BITS     = 10;
    const SEQUENCE_BITS      = 4;
    const TWEPOC = 1483200000000;   // 2017-01-01

    private $datacenterId;
    private $workerId;
    private $sequence;
    private $lastTimestamp;

    private static $instance;

    private function __construct() 
    {
        $this->setDatacenterId($this->getDatacenterId());
        $this->setWorkerId($this->getWorkerId());
    }

    public static function getInstance()
    {
        if (!self::$instance instanceof SnowFlake) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @desc    generate a snowflake id
     * @return  int | null
     * @throw   InvalidArgumentException
     */
    public function nextId() 
    {
        $timestamp = $this->getTimestamp();
        if ($timestamp == $this->lastTimestamp) {
            $sequence = $this->nextSequence() & $this->maxSequence(); 
            if ($sequence == 0) {
                $timestamp = $this->tilNextMillis($this->lastTimestamp);
            }
        } else {
            $this->sequence = 0;
            $sequence = $this->nextSequence();
        }
        $this->lastTimestamp = $timestamp;
        $time = intval($timestamp - self::TWEPOC) << $this->timestampShift();
        $datacenter = $this->datacenterId << $this->datacenterIdShift();
        $worker = $this->workerId << $this->workerIdShift();

        return PHP_INT_SIZE == 4 ? null : $this->mintId64($time, $datacenter, $worker, $sequence);
    }

    public function revert() {}

    private function getTimestamp() 
    {
        return intval(microtime(true) * 1000);
    }

    private function tilNextMillis($lastTimestamp) 
    {
        $timestamp = $this->getTimestamp();
        while ($timestamp <= $lastTimestamp) {
            $timestamp = $this->getTimestamp();
        }
        return $timestamp;
    }

    private function getDatacenterId() 
    {
        $ip  = $this->getIp();
        $max = $this->maxDatacenterId();
        return $ip ? intval(explode(".", $ip, 4)[3]) : mt_rand(0, $max);
    }

    private function getWorkerId() 
    {
        $pid = getmypid();
        $max = $this->maxWorkerId();
        return $pid !== false ? $pid & $max : mt_rand(0, $max);
    }

    private function nextSequence() 
    {
        return $this->sequence++;
    }

    private function maxDatacenterId()
    {
        return -1 ^ (-1 << self::DATACENTER_ID_BITS);
    }

    private function maxWorkerId() 
    {
        return -1 ^ (-1 << self::WORKER_ID_BITS);
    }

    private function maxSequence() 
    {
        return -1 ^ (-1 << self::SEQUENCE_BITS);
    }

    private function timestampShift() 
    {
        return  self::DATACENTER_ID_BITS + self::WORKER_ID_BITS + self::SEQUENCE_BITS;
    }

    private function datacenterIdShift() 
    {
        return self::WORKER_ID_BITS + self::SEQUENCE_BITS;
    }

    private function workerIdShift() 
    {
        return self::SEQUENCE_BITS;
    }

    private function mintId64($timestamp, $datacenterId, $workerId, $sequence)
    {
        return (string)$timestamp | $datacenterId | $workerId | $sequence;
    }

    private function setDatacenterId($datacenterId) 
    {
        if ($datacenterId > $this->maxDatacenterId() || $datacenterId < 0) {
            throw new InvalidArgumentException("invalid argument datacenterId");
        }
        $this->datacenterId = $datacenterId;
    }
    
    private function setWorkerId($workerId) 
    {
        if ($workerId > $this->maxWorkerId() || $workerId < 0) {
            throw new InvalidArgumentException("invalid argument workerId");
        }
        $this->workerId = $workerId;
    }

    private function getIp()
    {
        $ip = null;
        if (getenv("SINASRV_INTIP") !== false) {
            $ip = filter_var(getenv("SINASRV_INTIP"), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
        }
        if (!$ip && getenv("SERVER_ADDR") !== false) {
            $ip = filter_var(getenv("SERVER_ADDR"), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
        }
        return $ip;
    } 
}
