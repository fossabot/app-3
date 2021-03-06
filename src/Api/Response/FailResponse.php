<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Response;

use App\Api\Response\Base\AbstractMessageResponse;
use App\Enum\ApiStatus;

/**
 * for invalid requests.
 *
 * Class FailResponse
 */
class FailResponse extends AbstractMessageResponse
{
    public function __construct(string $message, int $errorCode)
    {
        parent::__construct(ApiStatus::FAIL, $message, $errorCode);
    }
}
