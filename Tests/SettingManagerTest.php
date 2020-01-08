<?php
/**
 * BSD 3-Clause License
 *
 * Copyright (c) 2020, TASoft Applications
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 *  Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 *  Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

/**
 * SettingManagerTest.php
 * skyline-settings
 *
 * Created on 2020-01-08 11:26 by thomas
 */

use PHPUnit\Framework\TestCase;
use Skyline\Setting\SettingManager;

class SettingManagerTest extends TestCase
{
    public function backendProvider() {
        static $MYSQL = NULL;
        static $SQLITE = NULL;
        if(!$MYSQL)
            $MYSQL = new \TASoft\Util\PDO("mysql:host=localhost;dbname=TASOFT_TEST;unix_socket=/tmp/mysql.sock", 'root', 'tasoftapps');
        if(!$SQLITE)
            $SQLITE = new \TASoft\Util\PDO("sqlite:Tests/tests.sqlite");

        return [
            [$SQLITE],
            [$MYSQL]
        ];
    }

    /**
     * @param $PDO
     * @dataProvider backendProvider
     */
    public function testTables(\TASoft\Util\PDO $PDO) {
        $PDO->query("DELETE FROM SKY_SETTING");

        $this->assertTrue(true);
    }

    /**
     * @param \TASoft\Util\PDO $PDO
     * @depends testTables
     * @dataProvider backendProvider
     */
    public function testDeclareSetting(\TASoft\Util\PDO $PDO) {
        $sm = new SettingManager($PDO);

        $sm->declareSetting("width", 250);

        $this->assertEquals(250, $sm->getSetting("width"));
        $this->assertEquals(250, $sm->getSetting("width", 'a_group'));
        $this->assertEquals(250, $sm->getSetting("width", NULL, 13));
        $this->assertEquals(250, $sm->getSetting("width", 'a_group', 13));

        $sm->declareSetting("width", 300, 'a_group');

        $this->assertEquals(250, $sm->getSetting("width"));
        $this->assertEquals(300, $sm->getSetting("width", 'a_group'));
        $this->assertEquals(250, $sm->getSetting("width", NULL, 13));
        $this->assertEquals(300, $sm->getSetting("width", 'a_group', 13));

        $sm->declareSetting("width", 400, NULL, 13);

        $this->assertEquals(250, $sm->getSetting("width"));
        $this->assertEquals(300, $sm->getSetting("width", 'a_group'));
        $this->assertEquals(400, $sm->getSetting("width", NULL, 13));
        $this->assertEquals(400, $sm->getSetting("width", 'a_group', 13));

        $sm->declareSetting("width", 500, 'a_group', 13);

        $this->assertEquals(250, $sm->getSetting("width"));
        $this->assertEquals(300, $sm->getSetting("width", 'a_group'));
        $this->assertEquals(400, $sm->getSetting("width", NULL, 13));
        $this->assertEquals(500, $sm->getSetting("width", 'a_group', 13));
    }

    /**
     * @param \TASoft\Util\PDO $PDO
     * @depends testDeclareSetting
     * @dataProvider backendProvider
     */
    public function testRemovingSetting(\TASoft\Util\PDO $PDO) {
        $sm = new SettingManager($PDO);

        $sm->removeSetting("width", 'a_group', 13);

        $this->assertEquals(250, $sm->getSetting("width"));
        $this->assertEquals(300, $sm->getSetting("width", 'a_group'));
        $this->assertEquals(400, $sm->getSetting("width", NULL, 13));
        $this->assertEquals(400, $sm->getSetting("width", 'a_group', 13));

        $sm->removeSetting("width");

        $this->assertNull($sm->getSetting("width"));
        $this->assertEquals(300, $sm->getSetting("width", 'a_group'));
        $this->assertEquals(400, $sm->getSetting("width", NULL, 13));
        $this->assertEquals(400, $sm->getSetting("width", 'a_group', 13));

        $sm->removeSetting("width", 'a_group');

        $this->assertNull($sm->getSetting("width"));
        $this->assertNull( $sm->getSetting("width", 'a_group'));
        $this->assertEquals(400, $sm->getSetting("width", NULL, 13));
        $this->assertEquals( 400, $sm->getSetting("width", 'a_group', 13));

        $sm->removeSetting("width", NULL, 13);

        $this->assertNull($sm->getSetting("width"));
        $this->assertNull( $sm->getSetting("width", 'a_group'));
        $this->assertNull( $sm->getSetting("width", NULL, 13));
        $this->assertNull( $sm->getSetting("width", 'a_group', 13));


        $this->assertEquals(0, $PDO->selectFieldValue ("SELECT count(id) AS C FROM SKY_SETTING", 'C'));
        }

    /**
     * @param \TASoft\Util\PDO $PDO
     * @depends testRemovingSetting
     * @dataProvider backendProvider
     */
    public function testGettingWholeSetting(\TASoft\Util\PDO $PDO) {
        $sm = new SettingManager($PDO);

        $sm->declareSetting("width", 250);
        $sm->declareSetting("width", 300, 'a_group');
        $sm->declareSetting("width", 400, NULL, 13);
        $sm->declareSetting("width", 500, 'a_group', 13);

        $this->assertEquals(4, $PDO->selectFieldValue ("SELECT count(id) AS C FROM SKY_SETTING", 'C'));

        $setting = $sm->getSetting("width", 'a_group', 13, false);
        $this->assertEquals([
            'name' => 'width',
            'value' => 500,
            'groupName' => 'a_group',
            'owner' => 13
        ], $setting);

        $setting = $sm->getSetting("width", 'a_group', NULL, false);
        $this->assertEquals([
            'name' => 'width',
            'value' => 300,
            'groupName' => 'a_group',
            'owner' => NULL
        ], $setting);


        $setting = $sm->getSetting("width", NULL, 13, false);
        $this->assertEquals([
            'name' => 'width',
            'value' => 400,
            'groupName' => NULL,
            'owner' => 13
        ], $setting);


        $setting = $sm->getSetting("width", NULL, NULL, false);
        $this->assertEquals([
            'name' => 'width',
            'value' => 250,
            'groupName' => NULL,
            'owner' => NULL
        ], $setting);


        $setting = $sm->getSetting("inexistent", NULL, NULL, false);
        $this->assertEquals([
            'name' => NULL,
            'value' => NULL,
            'groupName' => NULL,
            'owner' => NULL
        ], $setting);

        $sm->removeSettingAll("width");
    }
}
