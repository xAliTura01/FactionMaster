<?php

/*
 *
 *      ______           __  _                __  ___           __
 *     / ____/___ ______/ /_(_)___  ____     /  |/  /___ ______/ /____  _____
 *    / /_  / __ `/ ___/ __/ / __ \/ __ \   / /|_/ / __ `/ ___/ __/ _ \/ ___/
 *   / __/ / /_/ / /__/ /_/ / /_/ / / / /  / /  / / /_/ (__  ) /_/  __/ /  
 *  /_/    \__,_/\___/\__/_/\____/_/ /_/  /_/  /_/\__,_/____/\__/\___/_/ 
 *
 * FactionMaster - A Faction plugin for PocketMine-MP
 * This file is part of FactionMaster
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @author ShockedPlot7560 
 * @link https://github.com/ShockedPlot7560
 * 
 *
*/

namespace ShockedPlot7560\FactionMaster\Route\Faction;

use jojoe77777\FormAPI\CustomForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\Faction\BankMain;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class BankDeposit implements Route {

    const SLUG = "bankDeposit";

    public $PermissionNeed = [Ids::PERMISSION_BANK_DEPOSIT];
    public $backMenu;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $this->backMenu = RouterFactory::get(BankMain::SLUG);
    }

    /**
     * @param array|null $params Give to first item the message to print if wanted
     */
    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        $this->UserEntity = $User;
        $message = "";
        if (isset($params[0]) && \is_string($params[0])) $message = $params[0];
        $menu = $this->bankDeposit($message);
        $player->sendForm($menu);;
    }

    public function call() : callable{
        $backRoute = $this->backMenu;
        return function (Player $Player, $data) use ($backRoute) {
            if ($data === null) return;
            if ($data[1] !== "") {
                if (\is_integer(intval($data[1])) && intval($data[1]) > 1) {
                    $money = Main::getInstance()->EconomyAPI->myMoney($Player);
                    if (!\is_bool($money)) {
                        if (($money - $data[1]) >= 0) {
                            if (MainAPI::updateMoney(MainAPI::getFactionOfPlayer($Player->getName())->name, intval($data[1]), $Player->getName())) {
                                Utils::processMenu($backRoute, $Player, [Utils::getText($this->UserEntity->name, "SUCCESS_BANK_DEPOSIT")]);
                            }else{
                                $menu = $this->bankDeposit(Utils::getText($this->UserEntity->name, "ERROR"));
                                $Player->sendForm($menu);
                            }
                        }else{
                            $menu = $this->bankDeposit(Utils::getText($this->UserEntity->name, "NO_ENOUGH_MONEY_PLAYER"));
                            $Player->sendForm($menu);
                        }
                    }else{
                        $menu = $this->bankDeposit(Utils::getText($this->UserEntity->name, "ERROR"));
                        $Player->sendForm($menu);
                    }
                }else{
                    $menu = $this->bankDeposit(Utils::getText($this->UserEntity->name, "VALID_FORMAT"));
                    $Player->sendForm($menu);
                }
            }else{
                Utils::processMenu($backRoute, $Player);

            }
        };
    }

    private function bankDeposit(string $message = "") : CustomForm {
        $menu = new CustomForm($this->call());
        $menu->setTitle(Utils::getText($this->UserEntity->name, "BANK_DEPOSIT_TITLE"));
        $menu->addLabel($message);
        $menu->addInput(Utils::getText($this->UserEntity->name, "BANK_DEPOSIT_INPUT"));
        return $menu;
    }
}