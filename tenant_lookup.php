#!/usr/bin/php -q
<?php
/**
 * AGI: Resolve tenant from DID using application database (never trust caller tenant id).
 * Usage: AGI(tenant_lookup.php,${DID})
 */
require_once __DIR__ . '/agi_common.php';

$did = $argv[1] ?? '';
if ($did === '') {
    agi_verbose('tenant_lookup: missing DID');
    exit(1);
}

$row = agi_db_fetch_one(
    'SELECT d.tenant_id, t.account_code FROM dids d INNER JOIN tenants t ON t.id = d.tenant_id WHERE d.did_number = ? AND d.enabled = 1 LIMIT 1',
    [$did]
);

if (!$row) {
    agi_set_var('TENANT_ID', '');
    agi_set_var('ACCOUNT_CODE', '');
    exit(0);
}

agi_set_var('TENANT_ID', (string)$row['tenant_id']);
agi_set_var('ACCOUNT_CODE', $row['account_code']);
agi_set_var('CHANNEL(accountcode)', $row['account_code']);
exit(0);
