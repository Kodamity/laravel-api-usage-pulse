<?php

declare(strict_types=1);

/**
 * Created by PhpStorm.
 * Author: Misha Serenkov
 * Email: mi.serenkov@gmail.com
 * Date: 16.02.2025 15:10
 */

namespace Kodamity\Libraries\ApiUsagePulse\Enums;

enum ResponseStatusGroup: string
{
    case Informational = 'kdm_api_usage_informational';

    case Successful = 'kdm_api_usage_successful';

    case Redirection = 'kdm_api_usage_redirection';

    case ClientError = 'kdm_api_usage_client_error';

    case ServerError = 'kdm_api_usage_server_error';

    public static function fromStatusCode(int $statusCode): self
    {
        return match (true) {
            $statusCode >= 100 && $statusCode < 200 => self::Informational,
            $statusCode >= 200 && $statusCode < 300 => self::Successful,
            $statusCode >= 300 && $statusCode < 400 => self::Redirection,
            $statusCode >= 400 && $statusCode < 500 => self::ClientError,
            default => self::ServerError,
        };
    }
}
