<?php

declare(strict_types=1);

namespace Phpsu\Phpsu\Config;

final readonly class SyncMode
{
    public const array VIDEO_FORMATS = ['*.mp4', '*.webm', '*.mkv', '*.flv', '*.avi', '*.mov', '*.wmv', '*.mpg', '*.mpeg'];

    public const array ARCHIVE_FORMATS = [
        '*.dmg',
        '*.pkg',
        '*.deb',
        '*.rpm',
        '*.msi',
        '*.apk',
        '*.zip',
        '*.tar',
        '*.gz',
        '*.bz2',
        '*.7z',
        '*.rar',
        '*.iso',
        '*.img',
        '*.vhd',
        '*.vmdk',
        '*.ova',
        '*.ovf',
        '*.tar.gz',
        '*.tar.bz2',
        '*.tar.xz',
        '*.tar.zst',
        '*.tar.lz4',
        '*.tar.sz',
        '*.tar.lzma',
        '*.tar.lzo',
        '*.tar.lz',
        '*.tar.zstd',
        '*.tar.br',
        '*.tar.z',
        '*.tar.zs',
        '*.tar.lzop',
    ];

    /**
     * @param list<string> $timestampColumnNames
     * @param list<string> $filesInclude
     * @param list<string> $filesExclude
     * @param list<string> $databaseInclude
     * @param list<string> $databaseExclude tables will be excluded from the sync completely (will not be touched in any way)
     * @param list<string> $databaseVolatileInclude
     * @param list<string> $databaseVolatileExclude tables will not be touched unless the structure changes, then they will be truncated (not synced)
     */
    private function __construct(
        public array $timestampColumnNames = [],
        public array $filesInclude = ['.*'],
        public array $filesExclude = [],
        public array $databaseInclude = ['.*'],
        public array $databaseExclude = [],
        public array $databaseVolatileInclude = [],
        public array $databaseVolatileExclude = [],
    ) {
    }

    public static function typo3(): SyncMode
    {
        return SyncMode::create(
            timestampColumnNames: ['tstamp', 'ses_tstamp', 'updatedon', 'changed', 'created'],
            filesExclude: [
                '_processed_',
                'temp_/*',
                '_migrated/*',
                '*.webp',
                '*.pdf',
                ...self::VIDEO_FORMATS,
                ...self::ARCHIVE_FORMATS,
            ],
            databaseVolatileInclude: [
                '^static_.*',
                '^cache_.*',
                'sys_log',
                'be_sessions',
                'fe_sessions',
                'sys_http_report',
                'sys_messenger_messages',
                'sys_preview',
                'tx_webp_failed',
                'tx_ausio_domain_model_taskstate',
            ],
        );
    }

    public static function create(
        false|string|array $timestampColumnNames = [],
        bool|string|array $filesInclude = true,
        bool|string|array $filesExclude = false,
        bool|string|array $databaseInclude = true,
        bool|string|array $databaseExclude = false,
        bool|string|array $databaseVolatileInclude = false,
        bool|string|array $databaseVolatileExclude = false,
    ): SyncMode {
        foreach (func_get_args() as &$value) {
            if ($value === true) {
                $value = ['.*'];
            } elseif ($value === false) {
                $value = [];
            }

            if (is_string($value)) {
                $value = [$value];
            }

            (static fn(string ...$value): int => 0)(...$value);
        }

        return new SyncMode(
            filesInclude: $filesInclude,
            filesExclude: $filesExclude,
            databaseInclude: $databaseInclude,
            databaseExclude: $databaseExclude,
            databaseVolatileInclude: $databaseVolatileInclude,
            databaseVolatileExclude: $databaseVolatileExclude,
        );
    }

    public static function full(): SyncMode
    {
        return new SyncMode();
    }

    public static function everything(): SyncMode
    {
        return new SyncMode();
    }
}
