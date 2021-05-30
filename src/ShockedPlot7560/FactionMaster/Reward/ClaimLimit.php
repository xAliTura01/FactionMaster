<?php

namespace ShockedPlot7560\FactionMaster\Reward;

use ShockedPlot7560\FactionMaster\API\MainAPI;

class ClaimLimit extends Reward implements RewardInterface {

    public function __construct(int $value = 0)
    {
        $this->value = $value;
        $this->nameSlug = "REWARD_CLAIM_LIMIT_NAME";
        $this->type = RewardType::CLAIM_LIMIT;
    }

    public function executeGet(string $factionName, ?int $value = null) : bool {
        if ($value !== null) $this->setValue($value);
        return MainAPI::updateFactionOption($factionName, 'max_claim', $this->value);
    }

    public function executeCost(string $factionName, ?int $value = null) {
        if ($value !== null) $this->setValue($value);
        return ($result = MainAPI::updateFactionOption($factionName, 'max_claim', $this->value * -1)) === false ? "ERROR" : $result;
    }

}