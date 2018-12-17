<?php
declare(strict_types=1);

namespace PHPSu\Tests\Alpha;

use PHPSu\Alpha\TheInterface;
use PHPUnit\Framework\TestCase;

class TheInterfaceTest extends TestCase
{

    public function testGetCommands(): void
    {
        $interface = new TheInterface();
        $global = GlobalConfigTest::getGlobalConfig();
        $result = $interface->getCommands($global, 'production', 'local', 'local');
        $this->assertSame([
            'rsync  -e "ssh -F .phpsu/config/ssh_config" serverEu:/var/www/production/fileadmin/* /home/user/www/github/phpsu/fileadmin/',
            'rsync  -e "ssh -F .phpsu/config/ssh_config" serverEu:/var/www/production/uploads/* /home/user/www/github/phpsu/uploads/',
        ], $result);
    }
}
