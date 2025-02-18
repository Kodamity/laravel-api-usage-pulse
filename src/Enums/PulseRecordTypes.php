<?php

declare(strict_types=1);

/**
 * Created by PhpStorm.
 * Author: Misha Serenkov
 * Email: mi.serenkov@gmail.com
 * Date: 16.02.2025 18:50
 */

namespace Kodamity\Libraries\ApiUsagePulse\Enums;

enum PulseRecordTypes: string
{
    case RequestsStatisticsTotal = 'kdm_api_usage_requests_total';

    case RequestsStatisticsSuccessful = 'kdm_api_usage_requests_successful';
}
