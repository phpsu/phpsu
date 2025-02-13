<?php

declare(strict_types=1);

use Kanti\Secrets\Secrets;
use Phpsu\Phpsu\Config\Config;
use Phpsu\Phpsu\Config\Filesystem;
use Phpsu\Phpsu\Config\Instance;
use Phpsu\Phpsu\Config\MySql;
use Phpsu\Phpsu\Config\SyncMode;

return Config::create(
    sshConfig: './.ssh/config',
    sshKnownHostFile: './.ssh/known_hosts',
    syncModes: [
        'default' => SyncMode::typo3(),
        'products' => SyncMode::create(
            filesInclude: false,
            databaseInclude: [
                'tx_products_domain_model_.*',
            ],
        ),
        'full' => SyncMode::full(),
        'everything' => SyncMode::everything(),
    ],
    instances: [
        'production' => Instance::create(
            filesystem: Filesystem::create(
                path: '/var/www/production',
                sshHost: 'production.example.com',
            ),
            database: MySql::tcp(
                database: 'production',
                user: 'user',
                password: Secrets::convert('43g7v89tgb893fcg4nr8vc924ngw80vrcmhb439wmh3rn0x'),
                host: 'aws-rds-production.example.com',
                port: 1234,
            ),
        ),
        'testing' => Instance::create(
            filesystem: Filesystem::create(
                path: '/var/www/testing',
            ),
            database: MySql::socket(
                database: 'testing',
                user: 'user',
                password: Secrets::convert('43g7v89tgb893fcg4nr8vc924ngw80vrcmhb439wmh3rn0x'),
            ),
            sshHost: 'testing.example.com',
        ),
        'local' => Instance::create(
            filesystem: Filesystem::create(
                path: '/var/www/testing',
            ),
            database: MySql::tcp(
                database: 'project_12567',
                user: 'root',
                password: Secrets::convert('43g7v89tgb893fcg4nr8vc924ngw80vrcmhb439wmh3rn0x'),
                host: 'global-db-v8',
            ),
        ),
    ],
);
