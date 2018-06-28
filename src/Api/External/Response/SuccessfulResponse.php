<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\External\Response;

use App\Api\External\Response\Base\AbstractResponse;
use App\Enum\ApiStatus;

/**
 * for successful requests.
 *
 * Class SuccessfulResponse
 */
class SuccessfulResponse extends AbstractResponse
{
    public function __construct($data)
    {
        parent::__construct(ApiStatus::SUCCESS);
        $this->data = $data;
    }

    /**
     * @var object
     */
    private $data;

    /**
     * @return object
     */
    public function getData()
    {
        return $this->data;
    }
}