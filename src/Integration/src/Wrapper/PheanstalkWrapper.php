<?php

declare(strict_types=1);

namespace Integration\Wrapper;

use Pheanstalk\Pheanstalk;

class PheanstalkWrapper
{
    private Pheanstalk $pheanstalk;
    /** Очередь для хуков*/
    public const HOOK_QUEUE = 'hook_queue';
    public const AUTH_QUEUE = 'auth_queue';

    public function __construct()
    {
        $this->pheanstalk = Pheanstalk::create(getenv('BEANSTALK_HOST'));
    }

    /**
     * Получение клиента Pheanstalk
     * @return Pheanstalk
     */
    public function getPheanstalk(): Pheanstalk
    {
        return $this->pheanstalk;
    }
}
