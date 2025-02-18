<?php

declare(strict_types=1);

/**
 * Created by PhpStorm.
 * Author: Misha Serenkov
 * Email: mi.serenkov@gmail.com
 * Date: 16.02.2025 21:35
 */

namespace Kodamity\Libraries\ApiUsagePulse\Enums;

enum PulseRecordKeys: string
{
    case ResponsesStatistics = 'kdm_api_usage_responses_statistics';
}
