<?php
/**
 * Copyright (c) 2019 TASoft Applications, Th. Abplanalp <info@tasoft.ch>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Skyline\Setting;


use TASoft\Util\PDO;

class SettingManager implements SettingManagerInterface
{
    /** @var PDO */
    private $PDO;

    /**
     * SettingManager constructor.
     * @param PDO $PDO
     */
    public function __construct(PDO $PDO)
    {
        $this->PDO = $PDO;
    }


    /**
     * @return PDO
     */
    public function getPDO()
    {
        return $this->PDO;
    }

    public function getSetting($name, $group = NULL, $user = NULL, bool $valueOnly = true)
    {
        $PDO = $this->getPDO();

        $setting = [
            'name' => NULL,
            'value' => NULL,
            'groupName' => NULL,
            'owner' => NULL
        ];

        $field = "setting AS name, value, groupName, owner";

        if($group && $user) {
            if($s = $PDO->selectOne("
SELECT $field FROM SKY_SETTING
WHERE setting = ? AND groupName = ? AND owner = ?", [$name, $group, $user])) {
                $setting = $s;
                goto completed;
            }
        }

        if($user) {
            if($s = $PDO->selectOne("
SELECT $field FROM SKY_SETTING
WHERE setting = ? AND groupName IS NULL AND owner = ?", [$name, $user])) {
                $setting = $s;
                goto completed;
            }
        }

        if($group) {
            if($s = $PDO->selectOne("
SELECT $field FROM SKY_SETTING
WHERE setting = ? AND groupName = ? AND owner IS NULL", [$name, $group])) {
                $setting = $s;
                goto completed;
            }
        }


        if($s = $PDO->selectOne("
SELECT $field FROM SKY_SETTING
WHERE setting = ? AND groupName IS NULL AND owner IS NULL", [$name])) {
            $setting = $s;
            goto completed;
        }

        completed:
        return $valueOnly ? $setting["value"] : $setting;
    }

    public function declareSetting($name, $value, $group = NULL, $user = NULL)
    {
        if($group && $user) {
            if($id = $this->getPDO()->selectFieldValue("SELECT id FROM SKY_SETTING WHERE setting = ? AND groupName = ? AND owner = ? LIMIT 1", 'id', [$name, $group, $user])) {
                $this->getPDO()->inject("UPDATE SKY_SETTING SET value = ? WHERE id = $id")->send([
                    $value
                ]);
            } else {
                $this->getPDO()->inject("INSERT INTO SKY_SETTING (setting, groupName, owner, value) VALUES (?, ?, ?, ?)")->send([
                    $name,
                    $group,
                    $user,
                    $value
                ]);
            }
        } elseif($user) {
            if($id = $this->getPDO()->selectFieldValue("SELECT id FROM SKY_SETTING WHERE setting = ? AND groupName IS NULL AND owner = ? LIMIT 1", 'id', [$name, $user])) {
                $this->getPDO()->inject("UPDATE SKY_SETTING SET value = ? WHERE id = $id")->send([
                    $value
                ]);
            } else {
                $this->getPDO()->inject("INSERT INTO SKY_SETTING (setting, groupName, owner, value) VALUES (?, NULL, ?, ?)")->send([
                    $name,
                    $user,
                    $value
                ]);
            }
        } elseif($group) {
            if($id = $this->getPDO()->selectFieldValue("SELECT id FROM SKY_SETTING WHERE setting = ? AND groupName = ? AND owner IS NULL LIMIT 1", 'id', [$name, $group])) {
                $this->getPDO()->inject("UPDATE SKY_SETTING SET value = ? WHERE id = $id")->send([
                    $value
                ]);
            } else {
                $this->getPDO()->inject("INSERT INTO SKY_SETTING (setting, groupName, owner, value) VALUES (?, ?, NULL, ?)")->send([
                    $name,
                    $group,
                    $value
                ]);
            }
        } else {
            if($id = $this->getPDO()->selectFieldValue("SELECT id FROM SKY_SETTING WHERE setting = ? AND groupName IS NULL AND owner IS NULL LIMIT 1", 'id', [$name])) {
                $this->getPDO()->inject("UPDATE SKY_SETTING SET value = ? WHERE id = $id")->send([
                    $value
                ]);
            } else {
                $this->getPDO()->inject("INSERT INTO SKY_SETTING (setting, groupName, owner, value) VALUES (?, NULL, NULL, ?)")->send([
                    $name,
                    $value
                ]);
            }
        }
    }

    public function removeSetting($name, $group = NULL, $user = NULL)
    {
        if($group && $user) {
            $this->getPDO()->inject("DELETE FROM SKY_SETTING WHERE setting = ? AND owner = ? AND groupName = ?")->send([
                $name,
                $user,
                $group
            ]);
        }
        elseif($user) {
            $this->getPDO()->inject("DELETE FROM SKY_SETTING WHERE setting = ? AND owner = ? AND groupName IS NULL")->send([
                $name,
                $user
            ]);
        }
        elseif($group) {
            $this->getPDO()->inject("DELETE FROM SKY_SETTING WHERE setting = ? AND owner IS NULL AND groupName = ?")->send([
                $name,
                $group
            ]);
        }
        else {
            $this->getPDO()->inject("DELETE FROM SKY_SETTING WHERE setting = ? AND owner IS NULL AND groupName IS NULL")->send([
                $name
            ]);
        }
    }


    public function removeSettingAll($name)
    {
        $name = $this->getPDO()->quote($name);
        $this->getPDO()->exec("DELETE FROM SKY_SETTING WHERE setting = $name");
    }
}