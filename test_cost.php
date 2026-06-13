<?php
function getEndpointCost(string $path): int
{
    if (strpos($path, 'api/v1/webhooks') !== false) return 0;
    if (strpos($path, 'api/v1/usage') !== false) return 0;
    if (strpos($path, 'api/v1/companies/search') !== false) return 1;
    if (preg_match('#api/v1/companies/?$#', $path)) return 1;
    if (strpos($path, 'api/v1/') !== false) return 3;
    return 1;
}
echo getEndpointCost('/apiempresas/api/v1/companies');
