<?php

namespace ShockedPlot7560\FactionMaster\Route\Faction\Manage;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\Faction\Manage\Alliance\AllianceMainMenu;
use ShockedPlot7560\FactionMaster\Route\MainPanel;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ManageFactionMain implements Route {

    const SLUG = "manageMainFaction";

    public $PermissionNeed = [
        Ids::PERMISSION_CHANGE_FACTION_DESCRIPTION,
        Ids::PERMISSION_CHANGE_FACTION_MESSAGE,
        Ids::PERMISSION_CHANGE_FACTION_VISIBILITY,
        Ids::PERMISSION_MANAGE_LOWER_RANK_PERMISSIONS,
        Ids::PERMISSION_ACCEPT_ALLIANCE_DEMAND,
        Ids::PERMISSION_REFUSE_ALLIANCE_DEMAND,
        Ids::PERMISSION_SEND_ALLIANCE_INVITATION,
        Ids::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION,
        Ids::PERMISSION_LEVEL_UP,
        Ids::PERMISSION_BREAK_ALLIANCE
    ];
    public $backMenu;

    /** @var \jojoe77777\FormAPI\FormAPI */
    private $FormUI;
    /** @var array */
    private $buttons;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $this->FormUI = Main::getInstance()->FormUI;
        $this->backMenu = RouterFactory::get(MainPanel::SLUG);
    }

    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        $this->UserEntity = $User;
        $this->buttons = [];
        if ((isset($UserPermissions[Ids::PERMISSION_CHANGE_FACTION_DESCRIPTION]) && $UserPermissions[Ids::PERMISSION_CHANGE_FACTION_DESCRIPTION]) || $User->rank == Ids::OWNER_ID) $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_CHANGE_DESCRIPTION");
        if ((isset($UserPermissions[Ids::PERMISSION_CHANGE_FACTION_MESSAGE]) && $UserPermissions[Ids::PERMISSION_CHANGE_FACTION_MESSAGE]) || $User->rank == Ids::OWNER_ID) $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_CHANGE_MESSAGE");
        if ((isset($UserPermissions[Ids::PERMISSION_CHANGE_FACTION_VISIBILITY]) && $UserPermissions[Ids::PERMISSION_CHANGE_FACTION_VISIBILITY]) || $User->rank == Ids::OWNER_ID) $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_CHANGE_VISIBILITY");
        if ((isset($UserPermissions[Ids::PERMISSION_LEVEL_UP]) && $UserPermissions[Ids::PERMISSION_LEVEL_UP]) || $User->rank == Ids::OWNER_ID) $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_LEVEL_UP");
        if ((isset($UserPermissions[Ids::PERMISSION_MANAGE_LOWER_RANK_PERMISSIONS]) && $UserPermissions[Ids::PERMISSION_MANAGE_LOWER_RANK_PERMISSIONS]) || $User->rank == Ids::OWNER_ID) $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_CHANGE_PERMISSION");
        if ((isset($UserPermissions[Ids::PERMISSION_ACCEPT_ALLIANCE_DEMAND]) && $UserPermissions[Ids::PERMISSION_ACCEPT_ALLIANCE_DEMAND]) || 
            (isset($UserPermissions[Ids::PERMISSION_REFUSE_ALLIANCE_DEMAND]) && $UserPermissions[Ids::PERMISSION_REFUSE_ALLIANCE_DEMAND]) || 
            (isset($UserPermissions[Ids::PERMISSION_SEND_ALLIANCE_INVITATION]) && $UserPermissions[Ids::PERMISSION_SEND_ALLIANCE_INVITATION]) || 
            (isset($UserPermissions[Ids::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION]) && $UserPermissions[Ids::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION]) || 
            $User->rank == Ids::OWNER_ID) $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_MANAGE_ALLIANCE");
        $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_BACK");

        $message = "";
        if (isset($params[0])) $message = $params[0];
        $menu = $this->manageMainMembersMenu($message);
        $player->sendForm($menu);
    }

    public function call(): callable
    {
        $backMenu = $this->backMenu;
        return function (Player $player, $data) use ($backMenu) {
            if ($data === null) return;
            switch ($this->buttons[$data]) {
                case $this->buttons[\count($this->buttons) - 1]:
                    Utils::processMenu($backMenu, $player);
                    break;
                case Utils::getText($this->UserEntity->name, "BUTTON_MANAGE_ALLIANCE"):
                    Utils::processMenu(RouterFactory::get(AllianceMainMenu::SLUG), $player);
                    break;
                case Utils::getText($this->UserEntity->name, "BUTTON_CHANGE_DESCRIPTION"):
                    Utils::processMenu(RouterFactory::get(ChangeDescription::SLUG), $player);
                    break;
                case Utils::getText($this->UserEntity->name, "BUTTON_CHANGE_MESSAGE"):
                    Utils::processMenu(RouterFactory::get(ChangeMessage::SLUG), $player);
                    break;
                case Utils::getText($this->UserEntity->name, "BUTTON_CHANGE_VISIBILITY"):
                    Utils::processMenu(RouterFactory::get(ChangeVisibility::SLUG), $player);
                    break;
                case Utils::getText($this->UserEntity->name, "BUTTON_CHANGE_PERMISSION"):
                    Utils::processMenu(RouterFactory::get(ChangePermissionMain::SLUG), $player);
                    break;
                case Utils::getText($this->UserEntity->name, "BUTTON_LEVEL_UP"):
                    Utils::processMenu(RouterFactory::get(LevelUp::SLUG), $player);
                    break;
                default:
                    return;
                    break;
            }
        };
    }

    private function manageMainMembersMenu(string $message = "") : SimpleForm {
        $menu = new SimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        $menu->setTitle(Utils::getText($this->UserEntity->name, "MANAGE_FACTION_MAIN_TITLE"));
        if ($message !== "") $menu->setContent($message);
        return $menu;
    }

}